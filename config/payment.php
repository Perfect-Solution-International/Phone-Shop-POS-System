<?php

return [
    'default_driver' => env('PAYMENT_GATEWAY_DRIVER', 'sandbox'),

    'currency' => env('PAYMENT_GATEWAY_CURRENCY', 'LKR'),

    'gateways' => [
        'sandbox' => [
            'merchant_id' => env('PAYMENT_SANDBOX_MERCHANT_ID', 'SANDBOX_MERCHANT_001'),
            'secret' => env('PAYMENT_SANDBOX_SECRET', 'sandbox-secret-key-phone-pos-system-secure-token'),
            'is_sandbox' => true,
        ],
        'payhere' => [
            'merchant_id' => env('PAYHERE_MERCHANT_ID', ''),
            'secret' => env('PAYHERE_SECRET', ''),
            'is_sandbox' => env('PAYHERE_SANDBOX', true),
        ],
    ],
];

