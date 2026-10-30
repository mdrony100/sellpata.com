<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function pageIndex()
    {
        return view('admin.plans.index', ['plans' => Plan::orderBy('sort_order')->get()]);
    }

    public function index()
    {
        return response()->json(['plans' => Plan::orderBy('sort_order')->get()]);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:60'],
            'price_monthly' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'price_yearly' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'max_pages' => ['sometimes', 'integer', 'min:1', 'max:500'],
            'features' => ['sometimes', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('features', $data)) {
            // Unchecked boxes are absent from the request, so keep every known
            // flag and only flip the ones actually submitted.
            $known = array_keys($plan->features ?? []);
            $submitted = array_values(array_intersect($known, (array) $data['features']));
            $data['features'] = collect($known)->mapWithKeys(fn ($k) => [$k => in_array($k, $submitted, true)])->all();
        }

        $plan->fill($data)->save();
        AuditLog::record('plan.updated', $plan, ['changes' => array_keys($data)]);

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'plan' => $plan->fresh()]);
        }

        return back()->with('status', $plan->name.' প্ল্যান সংরক্ষণ হয়েছে।');
    }
}
