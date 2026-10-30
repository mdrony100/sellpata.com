<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Services\Platform\OrderTotals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    /** Resolve the tenant from the subdomain captured by the route domain. */
    private function store(string $username): Store
    {
        return Store::where('username_lower', strtolower($username))->firstOrFail();
    }

    public function show(Request $request, string $store, ?string $slug = null)
    {
        $model = $this->store($store);

        if (! $model->isActive()) {
            return response()->view('storefront-unavailable', ['store' => $model], 503);
        }

        $page = $slug
            ? $model->pages()->where('slug', $slug)->where('status', 'published')->first()
            : $model->primaryPage();

        // An old slug still lands on the page it used to point at.
        if (! $page && $slug) {
            $redirect = \App\Models\PageRedirect::where('store_id', $model->id)->where('slug', $slug)->first();
            $target = $redirect ? $model->pages()->whereKey($redirect->page_id)->where('status', 'published')->first() : null;

            if ($target) {
                return redirect()->to($model->publicUrl($target), 301);
            }
        }

        if (! $page || ! $page->liveContent()) {
            return response()->view('storefront-missing', ['store' => $model], 404);
        }

        return view('storefront', [
            'bootstrap' => [
                'store' => [
                    'username' => $model->username,
                    'name' => $model->display_name,
                    'phone' => $model->contact_phone,
                    'logo' => $model->logo_path,
                ],
                'page' => [
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'url' => $model->publicUrl($page),
                    'is_primary' => $page->is_primary,
                ],
                'design' => $page->template->design,
                'content' => $page->liveContent(),
                'delivery' => [
                    'enabled' => $model->delivery_charge_enabled,
                    'charge' => (int) $model->delivery_charge,
                ],
                'features' => $model->plan()->features ?? [],
                'order_endpoint' => '/checkout',
                'token' => csrf_token(),
            ],
        ]);
    }

    /** COD checkout: totals are computed server-side from published content. */
    public function order(Request $request, string $store, ?string $slug = null)
    {
        $model = $this->store($store);

        abort_unless($model->isActive(), 404);

        $page = $slug
            ? $model->pages()->where('slug', $slug)->where('status', 'published')->first()
            : $model->primaryPage();

        abort_if($page === null || $page->liveContent() === null, 404);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:80'],
            'customer_phone' => ['required', 'string', 'max:24'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:80'],
            'area' => ['nullable', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:500'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $totals = OrderTotals::fromPage($model, $page->liveContent(), (int) ($data['quantity'] ?? 1));

        // Duplicate guard: same buyer + same total inside a minute is a double tap.
        $recent = Order::where('store_id', $model->id)
            ->where('customer_phone', $data['customer_phone'])
            ->where('total', $totals['total'])
            ->where('created_at', '>', now()->subMinute())
            ->first();

        if ($recent) {
            return response()->json(['ok' => true, 'reference' => $recent->reference, 'duplicate' => true, 'total' => $recent->total]);
        }

        $order = DB::transaction(function () use ($model, $page, $data, $totals) {
            $content = $page->liveContent();

            $order = Order::create([
                'store_id' => $model->id,
                'page_id' => $page->id,
                'reference' => 'SP-'.strtoupper(Str::random(8)),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'address' => $data['address'],
                'city' => $data['city'] ?? null,
                'area' => $data['area'] ?? null,
                'note' => $data['note'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'delivery_charge' => $totals['delivery_charge'],
                'total' => $totals['total'],
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'status' => 'new',
                'snapshot' => [
                    'product' => $content['product'] ?? [],
                    'brand' => $content['brand'] ?? [],
                    'page_title' => $page->title,
                    'page_slug' => $page->slug,
                    'store_username' => $model->username,
                ],
                'ip' => request()->ip(),
            ]);

            $order->items()->create([
                'name' => $content['product']['name'] ?? $page->title,
                'variant' => null,
                'unit_price' => $totals['unit_price'],
                'quantity' => $totals['quantity'],
                'line_total' => $totals['subtotal'],
            ]);

            app(\App\Services\Platform\InvoiceService::class)->createOrderInvoice($order);

            return $order;
        });

        return response()->json([
            'ok' => true,
            'reference' => $order->reference,
            'total' => $order->total,
            'message' => 'ধন্যবাদ! আপনার অর্ডারটি পেয়েছি। অর্ডার নম্বর: '.$order->reference,
        ], 201);
    }
}
