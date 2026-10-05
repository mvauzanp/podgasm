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

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID', 'IDNP11IDNC434IDND5427IDZ60281'),
        'origin_address' => env('BITESHIP_ORIGIN_ADDRESS', 'Jl. Kertajaya No.188, Kertajaya, Kec. Gubeng, Surabaya, Jawa Timur 60281'),
        'origin_postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE', '60281'),
        'origin_phone' => env('BITESHIP_ORIGIN_PHONE', '081217195254'),
        'origin_name' => env('BITESHIP_ORIGIN_NAME', 'Podgasm Kertajaya Admin'),
        'origin_latitude' => env('BITESHIP_ORIGIN_LATITUDE', -7.279644),
        'origin_longitude' => env('BITESHIP_ORIGIN_LONGITUDE', 112.759850),
    ],

    'midtrans' => [
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
        'is_3ds' => env('MIDTRANS_IS_3DS', true),
    ],

    'olsera' => [
        'base_url' => env('OLSERA_API_BASE_URL', 'https://api-open.olsera.co.id/api/open-api/v1/id'),
        'app_id' => env('OLSERA_APP_ID'),
        'secret_key' => env('OLSERA_SECRET_KEY'),
        'access_token' => env('OLSERA_ACCESS_TOKEN'),
        'default_outlet_id' => env('OLSERA_DEFAULT_OUTLET_ID', '1'),
        'webhook_secret' => env('OLSERA_WEBHOOK_SECRET'),
        'mock_mode' => env('OLSERA_MOCK_MODE', true), // Default to true while waiting for credentials
    ],

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID', 'IDNP11IDNC434IDND5427IDZ60281'),
        'origin_address' => env('BITESHIP_ORIGIN_ADDRESS', 'Jl. Kertajaya No.188, Kertajaya, Kec. Gubeng, Surabaya, Jawa Timur 60281'),
        'origin_postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE', '60281'),
        'origin_phone' => env('BITESHIP_ORIGIN_PHONE', '081217195254'),
        'origin_name' => env('BITESHIP_ORIGIN_NAME', 'Podgasm Kertajaya Admin'),
    ],

];
