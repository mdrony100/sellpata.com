<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/** Returning customers sign in with the number they verified earlier. */
class PhoneLoginController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
        ], [], ['phone' => 'মোবাইল নম্বর']);

        $recent = OtpChallenge::where('phone', $data['phone'])
            ->where('purpose', 'login')
            ->where('created_at', '>', now()->subMinutes(15))
            ->count();

        if ($recent >= 3) {
            return response()->json(['message' => 'অনেকবার চেষ্টা হয়েছে। ১৫ মিনিট পর আবার চেষ্টা করুন।'], 429);
        }

        $user = User::where('phone', $data['phone'])->first();
        $code = (string) random_int(100000, 999999);

        // The same answer either way: the response must not reveal whether the
        // number has an account.
        if ($user) {
            OtpChallenge::create([
                'user_id' => $user->id,
                'phone' => $data['phone'],
                'purpose' => 'login',
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'max_attempts' => 5,
                'sends' => 1,
                'expires_at' => now()->addMinutes(10),
                'last_sent_at' => now(),
                'ip' => $request->ip(),
            ]);

            \Illuminate\Support\Facades\Log::info('login OTP', ['user' => $user->id, 'code' => $code]);
        }

        $response = ['ok' => true, 'message' => 'এই নম্বরে একটি কোড পাঠানো হয়েছে।'];

        if (config('otp.expose_test_code') && $user) {
            $response['test_code'] = $code;
        }

        return response()->json($response);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            'code' => ['required', 'string', 'digits:6'],
        ], [], ['phone' => 'মোবাইল নম্বর', 'code' => 'কোড']);

        $challenge = OtpChallenge::where('phone', $data['phone'])
            ->where('purpose', 'login')
            ->latest()
            ->first();

        if (! $challenge || ! $challenge->isUsable()) {
            return response()->json(['message' => 'কোডের সময় শেষ। আবার কোড পাঠান।'], 422);
        }

        if (! Hash::check($data['code'], $challenge->code_hash)) {
            $challenge->increment('attempts');

            return response()->json(['message' => 'কোডটি মেলেনি।'], 422);
        }

        $user = User::where('phone', $data['phone'])->first();

        if (! $user) {
            return response()->json(['message' => 'কোডটি মেলেনি।'], 422);
        }

        $challenge->forceFill(['consumed_at' => now()])->save();

        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();

        AuditLog::record('auth.login', $user, ['via' => 'otp'], $user->id);

        return response()->json([
            'ok' => true,
            'message' => 'সফলভাবে লগইন হয়েছে।',
            'redirect' => '/dashboard',
        ]);
    }
}
