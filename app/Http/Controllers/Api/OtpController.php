<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use App\Models\User;
use App\Services\Otp\OtpException;
use App\Services\Otp\OtpService;
use App\Support\PhoneNumber;
use App\Support\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    public function send(Request $request, string $publicId, DraftController $drafts)
    {
        $draft = $drafts->authorize($request, $publicId);

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:24'],
        ]);

        $phone = PhoneNumber::normalize($data['phone']);

        if ($phone === null) {
            throw ValidationException::withMessages([
                'phone' => 'সঠিক মোবাইল নম্বর লিখুন, যেমন ০১৭XXXXXXXX।',
            ]);
        }

        // SMS is not wired yet: the number alone claims the draft.
        if (config('otp.skip_verification')) {
            $user = $this->claimDraft($draft, $phone);

            Auth::login($user);
            $request->session()->regenerate();

            return response()->json([
                'ok' => true,
                'skip_otp' => true,
                'csrf' => csrf_token(),
                'message' => 'পেজটি আপনার অ্যাকাউন্টে যুক্ত হয়েছে।',
                'draft_id' => $draft->public_id,
                'user_id' => $user->id,
            ]);
        }

        try {
            $result = $this->otp->start($draft, $phone, VisitorIdentity::current($request), $request->ip());
        } catch (OtpException $e) {
            return response()->json([
                'error' => match ($e->reason) {
                    'provider_missing' => 'এসএমএস সেবা এখনো চালু হয়নি। অনুগ্রহ করে পরে আবার চেষ্টা করুন।',
                    'rate_limited' => 'অনেকবার কোড পাঠানো হয়েছে। এক ঘণ্টা পরে আবার চেষ্টা করুন।',
                    'cooldown' => 'নতুন কোড পাঠানোর আগে কিছুক্ষণ অপেক্ষা করুন।',
                    default => 'কোড পাঠানো যায়নি। আবার চেষ্টা করুন।',
                },
                'retry_after' => max(0, $e->retryAfter),
            ], $e->reason === 'provider_missing' ? 503 : 429);
        }

        return response()->json([
            'ok' => true,
            'phone_masked' => PhoneNumber::mask($phone),
            'expires_in' => (int) config('otp.ttl_seconds'),
            'resend_after' => (int) config('otp.resend_cooldown_seconds'),
            // Demo-code mode (OTP_EXPOSE_TEST_CODE=true): no SMS provider yet, so
            // the code is returned and shown on screen. Off by default in production.
            'test_code' => config('otp.expose_test_code') ? $result['code'] : null,
        ]);
    }

    public function verify(Request $request, string $publicId, DraftController $drafts)
    {
        $draft = $drafts->authorize($request, $publicId);

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:24'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $phone = PhoneNumber::normalize($data['phone']);

        if ($phone === null) {
            throw ValidationException::withMessages(['phone' => 'সঠিক মোবাইল নম্বর লিখুন।']);
        }

        try {
            $this->otp->verify($draft, $phone, $data['code'], VisitorIdentity::current($request));
        } catch (OtpException $e) {
            return response()->json([
                'error' => match ($e->reason) {
                    'expired' => 'কোডের সময় শেষ হয়েছে। আবার কোড পাঠান।',
                    'too_many_attempts' => 'অনেকবার ভুল কোড দেওয়া হয়েছে। আবার কোড পাঠান।',
                    default => 'কোডটি সঠিক নয়।',
                },
                'reason' => $e->reason,
            ], 422);
        }

        $user = $this->claimDraft($draft, $phone);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
            'message' => 'ফোন নম্বর যাচাই হয়েছে!',
            'draft_id' => $draft->public_id,
            'user_id' => $user->id,
        ]);
    }
    /** Ties a draft to an account, creating the account when the number is new. */
    private function claimDraft(Draft $draft, string $phone): User
    {
        return DB::transaction(function () use ($draft, $phone) {
            $locked = Draft::whereKey($draft->id)->lockForUpdate()->firstOrFail();

            abort_if($locked->user_id !== null, 409, 'এই ড্রাফট আগেই যুক্ত হয়েছে।');

            $user = User::where('phone', $phone)->lockForUpdate()->first();

            if (! $user) {
                $user = User::create([
                    'name' => 'SellPata ব্যবহারকারী',
                    'phone' => $phone,
                    // No password is ever shown or used in this phase; the
                    // account is phone-verified, not password-based.
                    'password' => bcrypt(Str::random(40)),
                    'phone_verified_at' => now(),
                ]);
            } elseif (! $user->phone_verified_at) {
                $user->forceFill(['phone_verified_at' => now()])->save();
            }

            $locked->forceFill([
                'user_id' => $user->id,
                'anonymous_identity_id' => null,
                'claimed_at' => now(),
                'status' => 'draft',
            ])->save();

            return $user;
        });
    }
}
