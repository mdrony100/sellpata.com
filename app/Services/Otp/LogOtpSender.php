<?php

namespace App\Services\Otp;

use Illuminate\Support\Facades\Log;

/**
 * Development sender: writes the code to storage/logs/otp.log so it can be
 * tested locally. It refuses to run in production, so the UI can never claim
 * an SMS was delivered when no provider is configured.
 */
class LogOtpSender implements OtpSender
{
    public function configured(): bool
    {
        return ! app()->environment('production');
    }

    public function send(string $phone, string $code): void
    {
        if (! $this->configured()) {
            throw new OtpException('provider_missing');
        }

        Log::channel('single')->info('[OTP] dev delivery', [
            'phone' => $phone,
            'code' => $code,
        ]);
    }
}
