<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UniSMS (primary SMS provider)
    |--------------------------------------------------------------------------
    |
    | https://unismsapi.com/docs/sms - Basic auth: the secret key is the
    | username, the password is empty. Never commit a real key; UNISMS_* lives
    | in .env only. Keep swappable: code reads through this block, not env()
    | directly, so another provider could be added alongside later.
    |
    */

    'unisms' => [
        'key'       => env('UNISMS_SECRET_KEY'),
        'sender_id' => env('UNISMS_SENDER_ID'),
        'base_url'  => env('UNISMS_BASE_URL', 'https://unismsapi.com/api'),
    ],

];
