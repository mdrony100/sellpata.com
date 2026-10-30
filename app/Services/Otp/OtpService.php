<?php

namespace App\Services\Otp;

use App\Models\AnonymousIdentity;
use App\Models\Draft;
use App\Models\OtpChallenge;
use App\Support\PhoneNumber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public const PURPOSE = 'claim_draft';

    public function sender(): OtpSender
    {
        return match (config('otp.driver')) {
            'log' => new LogOtpSender,
            default => new NullOtpSender,
        };
    }

    public function start(Draft $draft, string $phone, ?AnonymousIdentity $identity, ?string $ip = null): array
    {
        $sender = $this->sender();

        // Without an SMS provider the flow can still run in local/testing: the
        // code is shown on screen instead of being texted. Production keeps the
        // hard requirement so no one can claim a page without a real OTP.
        $devCodes = config('otp.expose_test_code') && ! app()->environment('production');

        if (! $sender->configured() && ! $devCodes) {
            throw new OtpException('provider_missing');
        }

        $hourWindow = Carbon::now()->subHour();
        $sentLastHour = OtpChallenge::where('phone', $phone)->where('created_at', '>=', $hourWindow)->sum('sends');

        if ($sentLastHour >= (int) config('otp.max_sends_per_hour')) {
            throw new OtpException('rate_limited', 3600);
        }

        $latest = OtpChallenge::where('phone', $phone)
            ->where('purpose', self::PURPOSE)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if ($latest && $latest->last_sent_at) {
            $readyAt = $latest->last_sent_at->copy()->addSeconds((int) config('otp.resend_cooldown_seconds'));

            if ($readyAt->isFuture()) {
                throw new OtpException('cooldown', (int) ceil(Carbon::now()->diffInSeconds($readyAt, false) * -1));
            }
        }

        // A new code always invalidates the previous one.
        OtpChallenge::where('phone', $phone)
            ->where('purpose', self::PURPOSE)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => Carbon::now()]);

        $length = (int) config('otp.code_length', 6);
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        $challenge = OtpChallenge::create([
            'phone' => $phone,
            'purpose' => self::PURPOSE,
            'code_hash' => Hash::make($code),
            'draft_id' => $draft->id,
            'anonymous_identity_id' => $identity?->id,
            'max_attempts' => (int) config('otp.max_attempts', 5),
            'expires_at' => Carbon::now()->addSeconds((int) config('otp.ttl_seconds', 300)),
            'last_sent_at' => Carbon::now(),
            'ip' => $ip,
        ]);

        if ($sender->configured()) {
            $sender->send($phone, $code);
        } else {
            Log::warning('otp: no provider configured, code shown on screen', ['phone' => PhoneNumber::mask($phone)]);
        }

        return [
            'challenge' => $challenge,
            'code' => $code,
        ];
    }

    public function verify(Draft $draft, string $phone, string $code, ?AnonymousIdentity $identity): OtpChallenge
    {
        $challenge = OtpChallenge::where('phone', $phone)
            ->where('purpose', self::PURPOSE)
            ->where('draft_id', $draft->id)
            ->latest('id')
            ->first();

        if (! $challenge || $challenge->consumed_at !== null) {
            throw new OtpException('invalid_code');
        }

        if ($challenge->isExpired()) {
            throw new OtpException('expired');
        }

        if ($challenge->attempts >= $challenge->max_attempts) {
            throw new OtpException('too_many_attempts');
        }

        if ($identity && $challenge->anonymous_identity_id && $challenge->anonymous_identity_id !== $identity->id) {
            throw new OtpException('invalid_code');
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');

            if ($challenge->fresh()->attempts >= $challenge->max_attempts) {
                throw new OtpException('too_many_attempts');
            }

            throw new OtpException('invalid_code');
        }

        $challenge->forceFill(['consumed_at' => Carbon::now()])->save();

        return $challenge;
    }
}
