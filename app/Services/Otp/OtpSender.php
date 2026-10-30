<?php

namespace App\Services\Otp;

interface OtpSender
{
    /** Whether this sender can actually deliver an SMS right now. */
    public function configured(): bool;

    public function send(string $phone, string $code): void;
}
