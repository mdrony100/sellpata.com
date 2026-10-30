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
    // Phone-only onboarding while the SMS provider is still being wired up.
    // The number is trusted as entered; codes come back on later.
    'skip_verification' => (bool) env('OTP_SKIP_VERIFICATION', env('APP_ENV', 'production') === 'local'),

    // Demo-code mode: the generated OTP is returned in the API response and shown
    // on screen (login, builder claim, phone change). On by default outside
    // production; in production only when OTP_EXPOSE_TEST_CODE=true is set.
    // Anyone can then sign in as any registered number, so turn it OFF as soon
    // as a real SMS provider is connected.
    'expose_test_code' => (bool) env('OTP_EXPOSE_TEST_CODE', env('APP_ENV', 'production') !== 'production'),
];
