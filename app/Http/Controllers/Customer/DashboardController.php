<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Platform\Entitlements;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        $store = $user->store;
        $impersonation = session('impersonator_id') ? [
            'admin_id' => session('impersonator_id'),
            'expires_at' => session('impersonation_expires'),
        ] : null;

        return response()->json([
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone],
            'store' => $store ? [
                'username' => $store->username,
                'name' => $store->display_name,
                'url' => $store->publicUrl(),
                'status' => $store->status,
                'plan' => $store->plan()->name,
                'plan_slug' => $store->plan()->slug,
                'delivery_charge' => $store->delivery_charge,
                'delivery_charge_enabled' => $store->delivery_charge_enabled,
                'contact_phone' => $store->contact_phone,
                'contact_email' => $store->contact_email,
            ] : null,
            'impersonation' => $impersonation,
        ]);
    }

    public function index(Request $request)
    {
        $store = $request->user()->store;

        if (! $store) {
            return response()->json(['store' => null]);
        }

        $orders = Order::where('store_id', $store->id);
        $byStatus = (clone $orders)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $entitlements = Entitlements::for($store);
        $published = $store->pages()->where('status', 'published')->count();

        return response()->json([
            'store' => ['username' => $store->username, 'name' => $store->display_name, 'url' => $store->publicUrl()],
            'plan' => ['name' => $store->plan()->name, 'max_pages' => $entitlements->maxPages()],
            'metrics' => [
                'orders_total' => (clone $orders)->count(),
                'orders_new' => $byStatus['new'] ?? 0,
                'orders_confirmed' => $byStatus['confirmed'] ?? 0,
                'orders_delivered' => $byStatus['delivered'] ?? 0,
                'orders_cancelled' => $byStatus['cancelled'] ?? 0,
                'delivered_value' => (int) (clone $orders)->where('status', 'delivered')->sum('total'),
                'pages_published' => $published,
                'pages_limit' => $entitlements->maxPages(),
                'pages_used' => $store->pages()->count(),
            ],
            'recent' => (clone $orders)->latest()->limit(5)->get(['reference', 'customer_name', 'total', 'status', 'payment_status', 'created_at'])
                ->map(fn ($o) => [
                    'reference' => $o->reference,
                    'customer_name' => $o->customer_name,
                    'total' => $o->total,
                    'status' => $o->status,
                    'payment_status' => $o->payment_status,
                    'created_at' => $o->created_at->toIso8601String(),
                ]),
            'reminders' => array_values(array_filter([
                $published === 0 ? 'আপনার ল্যান্ডিং পেজ এখনো প্রকাশ করা হয়নি।' : null,
                $store->contact_phone === null ? 'স্টোরের যোগাযোগ নম্বর যোগ করুন, যাতে ক্রেতা যোগাযোগ করতে পারে।' : null,
            ])),
        ]);
    }
}
