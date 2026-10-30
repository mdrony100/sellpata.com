<?php

return [
    // 'log' writes codes to storage/logs/otp.log (never in production),
    // 'null' refuses to send, so no false "code sent" is ever shown.
    'driver' => env('OTP_DRIVER', 'log'),
    'code_length' => 6,
    'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 300),
    'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN', 60),
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'max_sends_per_hour' => (int) env('OTP_MAX_SENDS_PER_HOUR', 5),
    // Local testing only: returns the code in the API response. Never true in production.
    // Until a real SMS provider is wired up, the generated code is shown on
    // screen so the flow can be walked end to end. Production never exposes it.
    'expose_test_code' => (bool) env('OTP_EXPOSE_TEST_CODE', env('APP_ENV', 'production') !== 'production'),
];
