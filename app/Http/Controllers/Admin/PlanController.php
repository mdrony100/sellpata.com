<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
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

        $plan->fill($data)->save();
        AuditLog::record('plan.updated', $plan, ['changes' => array_keys($data)]);

        return response()->json(['ok' => true, 'plan' => $plan->fresh()]);
    }
}
