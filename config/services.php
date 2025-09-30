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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'discord' => [
        'webhook' => env('DISCORD_WEBHOOK_URL'),

    ],

    'zasilkovna' => [
        'api_key' => env('ZASILKOVNA_API_KEY'),
        'wsdl' => env('ZASILKOVNA_WSDL'),
        'wsdl_bugfix' => env('ZASILKOVNA_WSDL_BUGFIX'),
    ],

    'balikovna' => [
    'api_token'       => env('BALIKOVNA_API_TOKEN'),
    'secret_key'      => env('BALIKOVNA_SECRET_KEY'),
    'customer_id'     => env('BALIKOVNA_CUSTOMER_ID'),
    'contract_number' => env('BALIKOVNA_CONTRACT_NUMBER'),
],

];
