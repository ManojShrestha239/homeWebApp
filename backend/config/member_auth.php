<?php

declare(strict_types=1);

return [
    // Normal login: short-lived, in minutes
    'ttl_minutes' => (int) env('MEMBER_JWT_TTL', 60),

    // "Remember me": longer-lived, in minutes (30 days)
    'remember_ttl_minutes' => (int) env('MEMBER_JWT_REMEMBER_TTL', 60 * 24 * 30),
];
