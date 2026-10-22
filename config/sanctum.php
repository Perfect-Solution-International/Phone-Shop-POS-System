<?php

return [
    'stateful' => [],
    'guard' => ['web'],
    'expiration' => 480,
    'token_prefix' => '',
    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
