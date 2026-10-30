<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\Platform\InvoiceService;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    private function store(Request $request)
    {
        $store = $request->user()->store;
        abort_if($store === null, 404);

        return $store;
    }

    private function owned(Request $request, Order $order): Order
    {
        abort_unless($order->store_id === $this->store($request)->id, 404);

        return $order;
    }

    private function payload(Order $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'address' => $order->address,
            'city' => $order->city,
            'area' => $order->area,
            'note' => $order->note,
            'subtotal' => $order->subtotal,
            'delivery_charge' => $order->delivery_charge,
            'total' => $order->total,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'created_at' => $order->created_at->toIso8601String(),
            'items' => $order->items->map(fn ($i) => ['name' => $i->name, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price, 'line_total' => $i->line_total]),
            'history' => $order->snapshot['history'] ?? [],
        ];
    }

    public function index(Request $request)
    {
        $store = $this->store($request);
        $query = Order::where('store_id', $store->id)->with('items')->latest();

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('reference', 'like', "%{$search}%")
                ->orWhere('customer_phone', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%"));
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($payment = $request->query('payment_status')) {
            $query->where('payment_status', $payment);
        }
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->paginate(min(50, (int) $request->query('per_page', 20)));

        return response()->json([
            'data' => collect($orders->items())->map(fn (Order $o) => $this->payload($o)),
            'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()],
            'statuses' => Order::STATUSES,
            'payment_statuses' => Order::PAYMENT_STATUSES,
        ]);
    }

    public function show(Request $request, Order $order)
    {
        return response()->json(['order' => $this->payload($this->owned($request, $order)->load('items'))]);
    }

    public function update(Request $request, Order $order)
    {
        $order = $this->owned($request, $order)->load('items');

        $data = $request->validate([
            'status' => ['sometimes', 'in:'.implode(',', Order::STATUSES)],
            'payment_status' => ['sometimes', 'in:'.implode(',', Order::PAYMENT_STATUSES)],
            'note' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $history = $order->snapshot['history'] ?? [];

        if (isset($data['status']) && $data['status'] !== $order->status) {
            $history[] = ['type' => 'status', 'from' => $order->status, 'to' => $data['status'], 'at' => now()->toIso8601String()];
            $order->status = $data['status'];
        }

        if (isset($data['payment_status']) && $data['payment_status'] !== $order->payment_status) {
            // COD money is only recognised when the seller explicitly says so.
            $history[] = ['type' => 'payment', 'from' => $order->payment_status, 'to' => $data['payment_status'], 'at' => now()->toIso8601String()];
            $order->payment_status = $data['payment_status'];
            $order->paid_at = $data['payment_status'] === 'paid' ? now() : null;

            if ($order->payment_status === 'paid') {
                Invoice::where('order_id', $order->id)->update(['status' => 'paid']);
            }
        }

        if (array_key_exists('note', $data)) {
            $history[] = ['type' => 'note', 'text' => $data['note'], 'at' => now()->toIso8601String()];
        }

        $snapshot = $order->snapshot;
        $snapshot['history'] = $history;
        $order->snapshot = $snapshot;
        $order->save();

        AuditLog::record('order.updated', $order, ['status' => $order->status, 'payment_status' => $order->payment_status], $request->user()->id);

        return response()->json(['ok' => true, 'order' => $this->payload($order->fresh('items'))]);
    }

    public function invoice(Request $request, Order $order, InvoiceService $invoices)
    {
        $order = $this->owned($request, $order);
        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();

        return response($invoices->pdf($invoice), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$invoice->number.'.pdf"',
        ]);
    }
}
