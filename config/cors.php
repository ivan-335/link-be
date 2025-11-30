<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://link-be.ddev.site',
        'https://localhost:5173',
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => false,
];
