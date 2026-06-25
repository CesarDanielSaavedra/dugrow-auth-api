<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Permite que el frontend (Next.js) le pegue a la API desde otro origen
    | (ej: localhost:3000 -> localhost:8000 en dev, o Vercel -> cPanel en prod).
    |
    | Usamos autenticación por token Bearer (no cookies), por eso
    | 'supports_credentials' => false y 'allowed_origins' => ['*'] es seguro.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
