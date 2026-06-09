<?php

/**
 * Laravel may read this if a CORS package is present.
 * Custom CORS is handled in App\Http\Middleware\Cors (see Kernel).
 * Keep supports_credentials false when using explicit origins (browser-safe).
 */
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => [
        'http://localhost:8081',
        'http://localhost:8082',
        'http://127.0.0.1:8081',
        'http://127.0.0.1:8082',
        'http://localhost:19006',
        'http://127.0.0.1:19006',
        'https://tradezell.com',
        'https://www.tradezell.com',
        'https://app.tradezell.com',
    ],
    'allowed_origins_patterns' => [],
    /*
     * Comma-separated origins merged by App\Http\Middleware\Cors (e.g. http://192.168.1.10:8081).
     * Read via config() so values survive `php artisan config:cache`.
     */
    'env_extra_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '')))),
    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'Origin', 'X-Requested-With'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => false,
];
