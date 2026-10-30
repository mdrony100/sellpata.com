<?php

namespace App\Services\Otp;

class NullOtpSender implements OtpSender
{
    public function configured(): bool
    {
        return false;
    }

    public function send(string $phone, string $code): void
    {
        throw new OtpException('provider_missing');
    }
}
