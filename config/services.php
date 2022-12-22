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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'oauth_server' => [
        'client_id' => env('OAUTH_SERVER_ID'),
        'client_secret' => env('OAUTH_SERVER_SECRET'),
        'redirect' => env('OAUTH_SERVER_REDIRECT_URI'),
        'callback' => env('OAUTH_SERVER_CALLBACK'),
        'uri' => env('OAUTH_SERVER_URI'),
    ],

    'path' => [
        'base_url' => env('APP_BASE_URL'),
    ],

    'hris_api' => [
        'base_uri' => env('HRIS_SERVICE_BASE_URL'),
        'secret' => env('SECRET_SERVICE_BASE_URL'),
    ],
];
