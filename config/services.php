<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model'  => App\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'verify_service_sid' => env('TWILIO_VERIFY_SERVICE_SID'),
    ],

    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'project_id' => env('FIREBASE_PROJECT_ID', 'piqdrop-644ce'),
        'credentials' => storage_path('app/firebase/firebase_credentials.json'),
    ],

    'cache_clear' => [
        'token' => env('CACHE_CLEAR_TOKEN'),
    ],

    'currency' => env('CURRENCY_CODE', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | MTN MoMo API
    |--------------------------------------------------------------------------
    |
    | Register at https://momodeveloper.mtn.com and subscribe to both the
    | "Collection" and "Disbursement" products to obtain your credentials.
    |
    | Required .env variables:
    |
    |   MOMO_BASE_URL=https://sandbox.momodeveloper.mtn.com
    |   MOMO_ENVIRONMENT=sandbox          # "sandbox" or "production"
    |   MOMO_CURRENCY=EUR                 # EUR for sandbox; GHS, UGX, etc. for production
    |   MOMO_CALLBACK_URL=https://your-api.com/api/momo/callback
    |
    |   MOMO_COLLECTION_PRIMARY_KEY=      # Ocp-Apim-Subscription-Key for Collection API
    |   MOMO_COLLECTION_USER_ID=          # API user UUID created via POST /v1_0/apiuser
    |   MOMO_COLLECTION_API_KEY=          # API key generated via POST /v1_0/apiuser/{userId}/apikey
    |
    |   MOMO_DISBURSEMENT_PRIMARY_KEY=    # Ocp-Apim-Subscription-Key for Disbursement API
    |   MOMO_DISBURSEMENT_USER_ID=        # API user UUID for disbursement product
    |   MOMO_DISBURSEMENT_API_KEY=        # API key for disbursement product
    |
    */
    'momo' => [
        'base_url'     => env('MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com'),
        'environment'  => env('MOMO_ENVIRONMENT', 'sandbox'),
        'currency'     => env('MOMO_CURRENCY', 'EUR'),
        'callback_url' => env('MOMO_CALLBACK_URL'),

        'collection' => [
            'primary_key' => env('MOMO_COLLECTION_PRIMARY_KEY'),
            'user_id'     => env('MOMO_COLLECTION_USER_ID'),
            'api_key'     => env('MOMO_COLLECTION_API_KEY'),
        ],

        'disbursement' => [
            'primary_key' => env('MOMO_DISBURSEMENT_PRIMARY_KEY'),
            'user_id'     => env('MOMO_DISBURSEMENT_USER_ID'),
            'api_key'     => env('MOMO_DISBURSEMENT_API_KEY'),
        ],
    ],

];
