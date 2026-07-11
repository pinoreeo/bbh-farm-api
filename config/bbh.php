<?php

return [
    'openssl_conf' => env('OPENSSL_CONF'),

    'admin' => [
        'name' => env('BBH_ADMIN_NAME', 'John Doe'),
        'email' => env('BBH_ADMIN_EMAIL', 'john.doe@example.com'),
        'password' => env('BBH_ADMIN_PASSWORD', 'password'),
    ],

    'farm' => [
        'name' => env('BBH_FARM_NAME', 'Bumiku Bumimu Hijau Farm'),
        'address' => env('BBH_FARM_ADDRESS', 'Ajibarang'),
        'phone' => env('BBH_FARM_PHONE', '08123456789'),
        'email' => env('BBH_FARM_EMAIL'),
    ],

    'pdf' => [
        'browser_path' => env('BBH_PDF_BROWSER_PATH'),
    ],
];
