<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Allows the Flutter Web app (Chrome, localhost) and the HTML/JS admin
    | dashboard to call the Laravel API without browser CORS errors.
    |
    | CORS_ALLOWED_ORIGINS (comma-separated) restricts which sites may call
    | the API from a browser, e.g.
    |   CORS_ALLOWED_ORIGINS=https://civilwatch.example.gov.ph,https://app.example.gov.ph
    | Unset = '*' (local development; the Flutter web app runs on a
    | different port). Set it on any server other people can reach. Auth is
    | by bearer token (supports_credentials stays false), so '*' does not
    | expose cookies, but it lets any website script the API.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*'))
    ))) ?: ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
