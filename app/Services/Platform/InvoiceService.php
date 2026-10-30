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
                'name' => config('platform.invoice.issuer.name'),
                'address' => config('platform.invoice.issuer.address'),
                'phone' => config('platform.invoice.issuer.phone'),
                'email' => config('platform.invoice.issuer.email'),
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

    public function pdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['store', 'subscription.plan']);
        $order = $invoice->order_id ? Order::with('items')->find($invoice->order_id) : null;

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf'),
            'fontDir' => [resource_path('fonts')],
            'fontdata' => [
                'hindsiliguri' => [
                    'R' => 'HindSiliguri-Regular.ttf',
                    'B' => 'HindSiliguri-Bold.ttf',
                ],
            ],
            'default_font' => 'hindsiliguri',
            'useOTL' => 0xFF,       // complex-script shaping, required for Bengali
            'useKerning' => true,
        ]);

        $mpdf->WriteHTML(view('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $order,
            'plan' => $invoice->subscription?->plan,
            'vatNote' => config('platform.invoice.vat_note'),
        ])->render());

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    private function nextNumber(string $prefix, int $storeId): string
    {
        return DB::transaction(function () use ($prefix, $storeId) {
            $count = Invoice::where('type', $prefix === 'ORD' ? Invoice::ORDER : Invoice::SUBSCRIPTION)->lockForUpdate()->count();

            return $prefix.'-'.now()->format('Y').'-'.str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        });
    }
}
