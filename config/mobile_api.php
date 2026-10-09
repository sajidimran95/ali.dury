<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Native app API (/api/v1/sale, /api/v1/delivery, /api/v1/customer)
    |--------------------------------------------------------------------------
    */

    // Increase when the API contract changes in a breaking way.
    'version' => '1.0.0',

    // Device token lifetime in days (0 = never expires; logout always revokes).
    'token_ttl_days' => (int) env('MOBILE_API_TOKEN_TTL_DAYS', 365),

    // Login attempts per minute per IP + username.
    'login_throttle_per_minute' => (int) env('MOBILE_API_LOGIN_THROTTLE', 10),
];
