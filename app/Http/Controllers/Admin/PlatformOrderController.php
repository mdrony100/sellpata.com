<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PlatformOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('store');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('reference', 'like', "%{$search}%")
                ->orWhere('customer_phone', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%"));
        }
        if ($storeId = $request->query('store_id')) {
            $query->where('store_id', $storeId);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(25);

        return response()->json([
            'data' => collect($orders->items())->map(fn (Order $o) => [
                'reference' => $o->reference,
                'store' => $o->store?->username,
                'customer_name' => $o->customer_name,
                'customer_phone' => $o->customer_phone,
                'total' => $o->total,
                'status' => $o->status,
                'payment_status' => $o->payment_status,
                'created_at' => $o->created_at->toIso8601String(),
            ]),
            'meta' => ['total' => $orders->total(), 'last_page' => $orders->lastPage()],
        ]);
    }
}
