<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Hier stehen die Zugangsdaten für externe Services wie Mailgun, Postmark,
    | AWS usw. Füge hier Stripe hinzu, damit du es über config() sauber
    | abrufen kannst.
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

    // 👇 Stripe hinzufügen
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'key'    => env('STRIPE_KEY'),
    ],
];
