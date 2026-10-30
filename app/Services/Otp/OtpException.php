<?php

namespace App\Services\Otp;

use RuntimeException;

class OtpException extends RuntimeException
{
    public function __construct(public readonly string $reason, public readonly int $retryAfter = 0)
    {
        parent::__construct($reason);
    }
}
