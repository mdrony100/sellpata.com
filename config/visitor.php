<?php

return [
    // Lifetime of the anonymous browser identity cookie.
    'cookie_days' => (int) env('VISITOR_COOKIE_DAYS', 30),

    // Anonymous drafts older than this are removed by the cleanup command.
    'draft_ttl_days' => (int) env('ANONYMOUS_DRAFT_TTL_DAYS', 30),
];
