<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/** Returning customers sign in with the number they verified earlier. */
class PhoneLoginController extends Controller
{
    public function send(Request $request)
    {
        $phone = $this->phoneFrom($request);

        $recent = OtpChallenge::where('phone', $phone)
            ->where('purpose', 'login')
            ->where('created_at', '>', now()->subMinutes(15))
            ->count();

        if ($recent >= 3) {
            return response()->json(['message' => 'অনেকবার চেষ্টা হয়েছে। ১৫ মিনিট পর আবার চেষ্টা করুন।'], 429);
        }

        $user = User::where('phone', $phone)->first();

        // SMS is not wired yet: the number alone signs the customer in.
        if (config('otp.skip_verification')) {
            if (! $user) {
                return response()->json([
                    'message' => 'এই নম্বরে কোনো অ্যাকাউন্ট নেই। নতুন? ল্যান্ডিং পেজ বানিয়ে শুরু করুন।',
                    'needs_account' => true,
                ], 404);
            }

            \Illuminate\Support\Facades\Auth::login($user);
            $request->session()->regenerate();

            AuditLog::record('auth.login', $user, ['via' => 'phone'], $user->id);

            return response()->json([
                'ok' => true,
                'skip_otp' => true,
                'csrf' => csrf_token(),
                'message' => 'সফলভাবে লগইন হয়েছে।',
                'redirect' => '/dashboard',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        // The same answer either way: the response must not reveal whether the
        // number has an account.
        if ($user) {
            OtpChallenge::create([
                'user_id' => $user->id,
                'phone' => $phone,
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

        // The normalised form is echoed so the field can show +880… after sending.
        $response = ['ok' => true, 'message' => 'এই নম্বরে একটি কোড পাঠানো হয়েছে।', 'phone' => $phone];

        if (config('otp.expose_test_code') && $user) {
            $response['test_code'] = $code;
        }

        return response()->json($response);
    }

    /**
     * Customers type their number freely: 01757571122, 8801757571122 or
     * +880 1757-571122. Accounts are stored in E.164 form, so every shape is
     * normalised before it is looked up or stored on a challenge.
     */
    private function phoneFrom(Request $request): string
    {
        $request->validate(['phone' => ['required', 'string', 'max:24']], [], ['phone' => 'মোবাইল নম্বর']);

        $phone = $this->normalizePhone((string) $request->input('phone'));

        if (! $phone) {
            throw ValidationException::withMessages([
                'phone' => 'সঠিক মোবাইল নম্বর দিন, যেমন 01757571122।',
            ]);
        }

        return $phone;
    }

    private function normalizePhone(string $raw): ?string
    {
        $hadPlus = str_starts_with(trim($raw), '+');
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === null || $digits === '') {
            return null;
        }

        // Local Bangladeshi shapes: 01757571122, 1757571122, 8801757571122.
        if (str_starts_with($digits, '880')) {
            return strlen($digits) === 13 ? '+'.$digits : null;
        }

        if (str_starts_with($digits, '0')) {
            return strlen($digits) === 11 ? '+88'.$digits : null;
        }

        if (preg_match('/^1\d{9}$/', $digits)) {
            return '+880'.$digits;
        }

        // Anything else is treated as an international number.
        if ($hadPlus && strlen($digits) >= 8 && strlen($digits) <= 15) {
            return '+'.$digits;
        }

        return null;
    }

    public function verify(Request $request)
    {
        $phone = $this->phoneFrom($request);
        $request->validate(['code' => ['required', 'string', 'digits:6']], [], ['code' => 'কোড']);
        $code = (string) $request->input('code');

        $challenge = OtpChallenge::where('phone', $phone)
            ->where('purpose', 'login')
            ->latest()
            ->first();

        if (! $challenge || ! $challenge->isUsable()) {
            return response()->json(['message' => 'কোডের সময় শেষ। আবার কোড পাঠান।'], 422);
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');

            return response()->json(['message' => 'কোডটি মেলেনি।'], 422);
        }

        $user = User::where('phone', $phone)->first();

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
