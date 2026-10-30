<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function pageIndex(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $users = User::with('store')
            ->when($search, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('store', fn ($s) => $s->where('username', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('users', 'search'));
    }

    public function pageShow(User $user)
    {
        return view('admin.customers.show', [
            'user' => $user,
            'store' => $user->store,
            'plans' => Plan::orderBy('sort_order')->get(),
        ]);
    }

    public function index(Request $request)
    {
        $query = User::query()->with('store');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('store', fn ($s) => $s->where('username', 'like', "%{$search}%")));
        }

        $users = $query->latest()->paginate(25);

        $rows = collect($users->items())->map(function (User $user) {
            $store = $user->store;
            $plan = $store?->plan();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'created_at' => $user->created_at?->toDateString(),
                'store' => $store ? [
                    'id' => $store->id,
                    'username' => $store->username,
                    'url' => $store->publicUrl(),
                    'status' => $store->status,
                    'pages' => $store->pages()->count(),
                    'orders' => $store->orders()->count(),
                    'plan' => $plan?->name,
                ] : null,
            ];
        });

        return response()->json(['data' => $rows, 'meta' => ['total' => $users->total(), 'last_page' => $users->lastPage()]]);
    }

    public function show(User $user)
    {
        $store = $user->store;

        return response()->json([
            'user' => ['id' => $user->id, 'name' => $user->name, 'phone' => $user->phone, 'email' => $user->email],
            'store' => $store ? [
                'id' => $store->id,
                'username' => $store->username,
                'url' => $store->publicUrl(),
                'status' => $store->status,
                'suspended_reason' => $store->suspended_reason,
                'plan' => $store->plan()->name,
                'pages' => $store->pages()->get(['id', 'title', 'slug', 'status', 'is_primary']),
                'orders' => $store->orders()->latest()->limit(10)->get(['reference', 'customer_name', 'total', 'status', 'payment_status', 'created_at']),
            ] : null,
            'subscription' => ($s = $store?->activeSubscription()) ? [
                'plan' => $s->plan->name,
                'source' => $s->source,
                'started_at' => $s->started_at->toDateString(),
                'ends_at' => $s->ends_at?->toDateString(),
            ] : null,
        ]);
    }

    public function suspend(Request $request, Store $store)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:200']]);

        $store->forceFill(['status' => 'suspended', 'suspended_reason' => $data['reason']])->save();
        AuditLog::record('store.suspended', $store, ['reason' => $data['reason']]);

        return $this->done('স্টোর স্থগিত করা হয়েছে।');
    }

    public function reactivate(Store $store)
    {
        $store->forceFill(['status' => 'active', 'suspended_reason' => null])->save();
        AuditLog::record('store.reactivated', $store);

        return $this->done('স্টোর আবার চালু হয়েছে।');
    }

    /** Plan changes here are manual assignments and always leave an audit trail. */
    public function assignPlan(Request $request, Store $store)
    {
        $data = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'note' => ['required', 'string', 'max:200'],
            'period' => ['nullable', 'in:monthly,yearly'],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);

        Subscription::where('store_id', $store->id)->where('status', 'active')->update(['status' => 'cancelled']);

        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'source' => 'manual',
            'billing_period' => $data['period'] ?? 'monthly',
            'started_at' => now(),
            'ends_at' => null,
            'assigned_by' => $request->user()->id,
            'note' => $data['note'],
        ]);

        app(\App\Services\Platform\InvoiceService::class)->createSubscriptionInvoice($subscription, 0, 'unpaid');

        AuditLog::record('plan.assigned', $store, ['plan' => $plan->slug, 'note' => $data['note'], 'assigned_by' => $request->user()->id]);

        return $this->done('প্ল্যান নির্ধারণ করা হয়েছে এবং অডিটে সংরক্ষণ হয়েছে।');
    }

    private function done(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('status', $message);
    }
}
