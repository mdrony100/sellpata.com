<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        abort_if(session('impersonator_id'), 403, 'গ্রাহক হিসেবে দেখার সময় এই পরিবর্তন করা যাবে না।');

        $user = $request->user();
        $store = $user->store;

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'email' => ['sometimes', 'nullable', 'email', 'max:160'],
            'store_name' => ['sometimes', 'string', 'max:120'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:24'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'max:160'],
            'delivery_charge_enabled' => ['sometimes', 'boolean'],
            'delivery_charge' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ]);

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }
        $user->save();

        if ($store) {
            $store->fill(array_filter([
                'display_name' => $data['store_name'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
            ], fn ($v) => $v !== null));

            if (array_key_exists('delivery_charge_enabled', $data)) {
                $store->delivery_charge_enabled = $data['delivery_charge_enabled'];
            }
            if (array_key_exists('delivery_charge', $data)) {
                $store->delivery_charge = $data['delivery_charge'];
            }
            $store->save();
        }

        AuditLog::record('profile.updated', $store, ['fields' => array_keys($data)], $user->id);

        return response()->json(['ok' => true]);
    }

    /** The store address is protected after launch; only support can change it. */
    public function username(Request $request)
    {
        return response()->json([
            'ok' => false,
            'message' => 'স্টোরের ঠিকানা প্রকাশের পর নিজে থেকে বদলানো যায় না, কারণ এতে সব পাবলিক লিংক বদলে যায়। বদলাতে চাইলে সহায়তা টিমের সঙ্গে যোগাযোগ করুন।',
        ], 422);
    }
}
