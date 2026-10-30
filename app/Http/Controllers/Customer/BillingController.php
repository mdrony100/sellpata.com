<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Platform\InvoiceService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function plans(Request $request)
    {
        $store = $request->user()->store;
        $current = $store?->plan();

        return response()->json([
            'current' => $current?->slug,
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get()->map(fn (Plan $p) => [
                'slug' => $p->slug,
                'name' => $p->name,
                'price_monthly' => $p->price_monthly,
                'price_yearly' => $p->price_yearly,
                'max_pages' => $p->max_pages,
                'features' => $p->features,
                'purchasable' => $p->isPurchasable(),
                'current' => $current?->slug === $p->slug,
            ]),
        ]);
    }

    public function request(Request $request, Plan $plan)
    {
        $store = $request->user()->store;
        abort_if($store === null, 409);

        if (! $plan->isPurchasable()) {
            // No price/checkout configured yet — never fake a subscription.
            return response()->json([
                'ok' => false,
                'code' => 'contact',
                'message' => 'এই প্ল্যানের দাম এখনো নির্ধারণ করা হয়নি। বিস্তারিত জানতে যোগাযোগ করুন।',
            ], 200);
        }

        AuditLog::record('plan.purchase_requested', $store, ['plan' => $plan->slug], $request->user()->id);

        return response()->json([
            'ok' => false,
            'code' => 'no_gateway',
            'message' => 'অনলাইন পেমেন্ট এখনো চালু হয়নি। বিস্তারিত জানতে যোগাযোগ করুন।',
        ], 200);
    }

    /**
     * Dropping to the Free plan needs no billing, so the customer can do it
     * themselves. Choosing which pages stay active is mandatory above the limit.
     */
    public function downgrade(Request $request, Plan $plan)
    {
        $store = $request->user()->store;
        abort_if($store === null, 409);

        if ($plan->slug !== Plan::FREE) {
            return response()->json([
                'ok' => false,
                'code' => 'contact',
                'message' => 'প্ল্যান পরিবর্তনের জন্য যোগাযোগ করুন। পেমেন্ট ব্যবস্থা চালু হলে এখান থেকেই করা যাবে।',
            ], 200);
        }

        $data = $request->validate(['page_ids' => ['array'], 'page_ids.*' => ['integer']]);
        $keepIds = $data['page_ids'] ?? [];

        $published = $store->pages()->where('status', 'published')->get();

        if ($published->count() > $plan->max_pages) {
            $keep = $published->whereIn('id', $keepIds);

            if ($keep->count() !== $plan->max_pages) {
                return response()->json([
                    'ok' => false,
                    'code' => 'choose_pages',
                    'message' => "ফ্রি প্ল্যানে {$plan->max_pages}টি পেজ সক্রিয় রাখা যাবে। কোন পেজগুলো রাখবেন বেছে নিন।",
                    'published' => $published->map(fn ($p) => ['id' => $p->id, 'title' => $p->title, 'is_primary' => $p->is_primary]),
                    'required' => $plan->max_pages,
                ], 422);
            }

            foreach ($published as $page) {
                if (! $keep->contains('id', $page->id)) {
                    $page->forceFill(['status' => 'draft', 'is_primary' => false])->save();
                }
            }

            if (! $keep->contains(fn ($p) => $p->is_primary)) {
                $store->pages()->where('is_primary', true)->update(['is_primary' => false]);
                $keep->first()->forceFill(['is_primary' => true])->save();
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($store, $plan, $request, $published) {
            $store->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

            \App\Models\Subscription::create([
                'store_id' => $store->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'source' => 'free',
                'started_at' => now(),
                'note' => 'গ্রাহক নিজে ফ্রি প্ল্যানে নেমেছেন',
            ]);

            AuditLog::record('plan.downgraded', $store, ['plan' => $plan->slug, 'unpublished' => $published->count()], $request->user()->id);
        });

        return response()->json(['ok' => true, 'message' => 'আপনি এখন ফ্রি প্ল্যানে আছেন। বন্ধ করা পেজগুলোর কনটেন্ট সংরক্ষিত আছে।']);
    }

    public function invoices(Request $request)
    {
        $store = $request->user()->store;
        abort_if($store === null, 409);

        $invoices = Invoice::where('store_id', $store->id)
            ->where('type', Invoice::SUBSCRIPTION)
            ->latest('issued_at')
            ->get();

        return response()->json([
            'invoices' => $invoices->map(fn (Invoice $i) => [
                'id' => $i->id,
                'number' => $i->number,
                'amount' => $i->amount,
                'currency' => $i->currency,
                'status' => $i->status,
                'issued_at' => $i->issued_at->toIso8601String(),
                'plan' => Subscription::find($i->subscription_id)?->plan?->name,
                'payment_reference' => $i->payment_reference,
            ]),
            'subscription' => ($s = $store->activeSubscription()) ? [
                'plan' => $s->plan->name,
                'status' => $s->status,
                'started_at' => $s->started_at->toIso8601String(),
                'ends_at' => $s->ends_at?->toIso8601String(),
                'source' => $s->source,
            ] : null,
        ]);
    }

    public function download(Request $request, Invoice $invoice, InvoiceService $invoices)
    {
        $store = $request->user()->store;
        abort_unless($store && $invoice->store_id === $store->id, 404);

        return response($invoices->pdf($invoice), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$invoice->number.'.pdf"',
        ]);
    }
}
