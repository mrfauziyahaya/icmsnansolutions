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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token'    => env('WHATSAPP_ACCESS_TOKEN'),
        'admin_number'    => env('WHATSAPP_ADMIN_NUMBER', '60129379257'),
    ],

    // Cloudflare Turnstile — anti-spam on the public checkout form.
    // When keys are absent the check is skipped (dev convenience) and a warning is logged.
    'turnstile' => [
        'site_key'   => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'payments' => [
        // Hard limits on the public custom-amount field (MYR).
        'min_amount'  => env('PAYMENT_MIN_AMOUNT', 1),
        'max_amount'  => env('PAYMENT_MAX_AMOUNT', 50000),
        // BNPL providers won't accept tiny amounts; below this they're hidden.
        'bnpl_min'    => env('PAYMENT_BNPL_MIN', 30),
    ],

    'chip' => [
        'api_key'  => env('CHIP_API_KEY'),
        'brand_id' => env('CHIP_BRAND_ID'),
        'base_url' => env('CHIP_BASE_URL', env('CHIP_ENDPOINT', 'https://gate.chip-in.asia/api/v1')),
        // RSA public key used to verify the X-Signature header on webhooks.
        'webhook_public_key' => env('CHIP_PUBLIC_KEY_FOR_WEBHOOK'),
    ],

    'fiuu' => [
        'merchant_id' => env('FIUU_MERCHANT_ID'),
        'verify_key'  => env('FIUU_VERIFY_KEY'),
        'secret_key'  => env('FIUU_SECRET_KEY'),
        // Sandbox uses sandbox-payment.fiuu.com / sandbox-api.fiuu.com hosts.
        'sandbox'     => env('FIUU_SANDBOX', true),
        // Whether "Use extended format for Verify Payment" is enabled in the Fiuu
        // merchant portal. If enabled, currency is included in the vcode hash.
        // Must match the portal setting or the hosted page rejects the request.
        'vcode_with_currency' => env('FIUU_VCODE_WITH_CURRENCY', false),
    ],

    'atome' => [
        'partner_id' => env('ATOME_PARTNER_ID'),
        'secret_key' => env('ATOME_SECRET_KEY'),
        'base_url'   => env('ATOME_BASE_URL'),
    ],

    'ahapay' => [
        'api_key'     => env('AHAPAY_API_KEY'),
        'secret_key'  => env('AHAPAY_SECRET_KEY'),   // shared secret for callback HMAC
        'base_url'    => env('AHAPAY_BASE_URL'),
        'merchant_id' => env('AHAPAY_MERCHANT_ID'),  // optional external_merchant_id
    ],

    // senangPay runs on DOKU's Malaysia API (HMAC-signed, Client-Id header).
    'senangpay' => [
        'client_id'  => env('SENANGPAY_CLIENT_ID'),
        'secret_key' => env('SENANGPAY_SECRET_KEY'),
        // DOKU API host: https://api-sandbox.doku.com (sandbox) / https://api.doku.com (live)
        'base_url'   => env('SENANGPAY_BASE_URL'),
    ],

];
