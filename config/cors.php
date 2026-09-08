<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register'], // أضف أي مسارات تحتاجها
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // ضروري جداً إذا كنت ترسل الـ Token في الـ Headers
];
