<?php

return [
    /*
    | Unclaimed anonymous drafts are removed after this many days without an
    | edit. Claimed drafts (attached to an account) are never pruned.
    */
    'retention_days' => (int) env('DRAFT_RETENTION_DAYS', 30),
];
