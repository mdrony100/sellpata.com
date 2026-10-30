<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /** Step one of changing the login number: send a code to the new one. */
    public function sendPhoneOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/', 'unique:users,phone'],
        ], [], ['phone' => 'মোবাইল নম্বর']);

        $user = $request->user();

        $recent = \App\Models\OtpChallenge::where('user_id', $user->id)
            ->where('purpose', 'phone_change')
            ->where('created_at', '>', now()->subMinutes(15))
            ->count();

        if ($recent >= 3) {
            return response()->json(['message' => 'অনেকবার চেষ্টা হয়েছে। ১৫ মিনিট পর আবার চেষ্টা করুন।'], 429);
        }

        $code = (string) random_int(100000, 999999);

        \App\Models\OtpChallenge::create([
            'user_id' => $user->id,
            'phone' => $data['phone'],
            'purpose' => 'phone_change',
            'code_hash' => \Illuminate\Support\Facades\Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'sends' => 1,
            'expires_at' => now()->addMinutes(10),
            'last_sent_at' => now(),
            'ip' => $request->ip(),
        ]);

        \Illuminate\Support\Facades\Log::info('phone change OTP', ['user' => $user->id, 'code' => $code]);

        $response = ['ok' => true, 'message' => 'নতুন নম্বরে একটি কোড পাঠানো হয়েছে।'];

        if (config('otp.expose_test_code')) {
            $response['test_code'] = $code;
        }

        return response()->json($response);
    }

    /** Step two: the code proves the new number, then it replaces the old one. */
    public function verifyPhoneOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/', 'unique:users,phone'],
            'code' => ['required', 'string', 'digits:6'],
        ], [], ['phone' => 'মোবাইল নম্বর', 'code' => 'কোড']);

        $user = $request->user();

        $challenge = \App\Models\OtpChallenge::where('user_id', $user->id)
            ->where('purpose', 'phone_change')
            ->where('phone', $data['phone'])
            ->latest()
            ->first();

        if (! $challenge || ! $challenge->isUsable()) {
            return response()->json(['message' => 'কোডের সময় শেষ। আবার কোড পাঠান।'], 422);
        }

        if (! \Illuminate\Support\Facades\Hash::check($data['code'], $challenge->code_hash)) {
            $challenge->increment('attempts');

            return response()->json(['message' => 'কোডটি মেলেনি।'], 422);
        }

        $old = $user->phone;

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $challenge, $data, $old) {
            $user->forceFill(['phone' => $data['phone']])->save();
            $challenge->forceFill(['consumed_at' => now()])->save();

            \App\Models\AuditLog::record('user.phone_changed', $user, ['from' => $old, 'to' => $data['phone']], $user->id);
        });

        return response()->json(['ok' => true, 'message' => 'লগইনের নম্বর বদলানো হয়েছে।', 'phone' => $data['phone']]);
    }

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
