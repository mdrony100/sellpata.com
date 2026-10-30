<?php

namespace App\Services\Platform;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

/**
 * Two invoice families live here on purpose:
 *  - order    : a seller issues it to their buyer
 *  - subscription : SellPata issues it to the store owner
 * They never share numbering or records.
 */
class InvoiceService
{
    public function createOrderInvoice(Order $order): Invoice
    {
        $store = $order->store;

        return Invoice::create([
            'number' => $this->nextNumber('ORD', $store->id),
            'type' => Invoice::ORDER,
            'store_id' => $store->id,
            'order_id' => $order->id,
            'amount' => $order->total,
            'currency' => 'BDT',
            'status' => 'unpaid',           // COD is collected later, never on creation
            'seller_snapshot' => [
                'name' => $store->display_name,
                'username' => $store->username,
                'phone' => $store->contact_phone,
                'email' => $store->contact_email,
                'url' => $store->publicUrl(),
            ],
            'buyer_snapshot' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'address' => $order->address,
                'city' => $order->city,
                'area' => $order->area,
            ],
            'issued_at' => now(),
        ]);
    }

    public function createSubscriptionInvoice(Subscription $subscription, int $amount, string $status, ?string $reference = null): Invoice
    {
        $store = $subscription->store;

        return Invoice::create([
            'number' => $this->nextNumber('SUB', $store->id),
            'type' => Invoice::SUBSCRIPTION,
            'store_id' => $store->id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'currency' => 'BDT',
            'status' => $status,
            'payment_reference' => $reference,
            'seller_snapshot' => [
                'name' => \App\Services\Platform\PlatformSettings::get('platform.invoice.issuer.name', 'SellPata'),
                'address' => \App\Services\Platform\PlatformSettings::get('platform.invoice.issuer.address', ''),
                'phone' => \App\Services\Platform\PlatformSettings::get('platform.invoice.issuer.phone', ''),
                'email' => \App\Services\Platform\PlatformSettings::get('platform.invoice.issuer.email', ''),
            ],
            'buyer_snapshot' => [
                'name' => $store->display_name,
                'username' => $store->username,
                'phone' => $store->user?->phone,
                'email' => $store->contact_email,
            ],
            'issued_at' => now(),
        ]);
    }

    /**
     * mPDF cannot be installed in this environment (Composer is unusable here and
     * it has no Bengali shaping without it), so invoices are rendered by headless
     * Chromium: correct Bengali shaping, embedded fonts, real PDF bytes.
     * If Chromium is absent we fail loudly instead of returning a broken file.
     */
    public function pdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['store', 'subscription.plan']);
        $order = $invoice->order_id ? Order::with('items')->find($invoice->order_id) : null;

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $order,
            'plan' => $invoice->subscription?->plan,
            'vatNote' => \App\Services\Platform\PlatformSettings::get('platform.invoice.vat_note', ''),
            'fontUrl' => 'file://'.resource_path('fonts/HindSiliguri-Regular.ttf'),
        ])->render();

        $binary = $this->chromium();
        $dir = storage_path('app/invoices');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $base = $dir.'/'.$invoice->number.'-'.uniqid();
        file_put_contents($base.'.html', $html);

        $command = sprintf(
            '%s --headless --disable-gpu --no-sandbox --no-pdf-header-footer --print-to-pdf=%s %s 2>&1',
            escapeshellarg($binary),
            escapeshellarg($base.'.pdf'),
            escapeshellarg('file://'.$base.'.html')
        );
        exec($command, $output, $code);

        if ($code !== 0 || ! is_file($base.'.pdf') || filesize($base.'.pdf') < 1000) {
            @unlink($base.'.html');
            throw new \RuntimeException('ইনভয়েস PDF তৈরি করা যায়নি (PDF রেন্ডারার পাওয়া যায়নি)।');
        }

        $bytes = file_get_contents($base.'.pdf');
        @unlink($base.'.html');
        @unlink($base.'.pdf');

        return $bytes;
    }

    private function chromium(): string
    {
        foreach (['/usr/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome'] as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('PDF রেন্ডারার (Chromium) সার্ভারে নেই—ইনভয়েস তৈরি করা যাবে না।');
    }

    private function nextNumber(string $prefix, int $storeId): string
    {
        return DB::transaction(function () use ($prefix, $storeId) {
            $count = Invoice::where('type', $prefix === 'ORD' ? Invoice::ORDER : Invoice::SUBSCRIPTION)->lockForUpdate()->count();

            return $prefix.'-'.now()->format('Y').'-'.str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        });
    }
}
