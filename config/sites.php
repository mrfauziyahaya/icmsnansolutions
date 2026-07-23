<?php

/*
|--------------------------------------------------------------------------
| Sites (multi-domain)
|--------------------------------------------------------------------------
|
| One codebase serves several domains. The request host decides which site
| is active, and that drives: gateway credentials, gateway labels, Turnstile
| keys, branding, the reference prefix, and which routes are reachable.
|
| Credentials live here (not config/services.php) because they differ per
| site. env() is only ever called from config files so config:cache is safe.
|
*/

return [

    // Used for CLI/queue contexts and as the fallback when a host is unknown.
    'default' => env('DEFAULT_SITE', 'nansolutions'),

    'sites' => [

        // ── NAN Solutions — the full application ─────────────────────────
        'nansolutions' => [
            'label'   => 'NAN Solutions',
            'domains' => [
                'nansolutions.com.my',
                'www.nansolutions.com.my',
                'icms.nansolutions.com.my',
                'www.icms.nansolutions.com.my',
                'staging.nansolutions.com.my',
                'www.staging.nansolutions.com.my',
                'localhost',
                '127.0.0.1',
            ],

            // '*' = every route is reachable on this site.
            'routes' => ['*'],

            'reference_prefix' => 'PAY',
            'whatsapp_link'    => 'https://wa.link/aib22q',

            // Branding. Null company/logo means "fall back to admin Settings".
            'company' => null,
            'logo'    => null,

            'turnstile' => [
                'site_key'   => env('TURNSTILE_SITE_KEY'),
                'secret_key' => env('TURNSTILE_SECRET_KEY'),
            ],

            'gateways' => [
                'chip' => [
                    'label'   => 'CHIP',
                    // Two selectable options at checkout (FPX / card).
                    'methods' => [
                        ['method' => 'fpx',  'label' => 'CHIP — FPX (Maybank, CIMB, dll.)'],
                        ['method' => 'card', 'label' => 'CHIP — Kad Kredit / Debit'],
                    ],
                    'config'  => [
                        'api_key'            => env('CHIP_API_KEY'),
                        'brand_id'           => env('CHIP_BRAND_ID'),
                        'base_url'           => env('CHIP_BASE_URL', env('CHIP_ENDPOINT', 'https://gate.chip-in.asia/api/v1')),
                        'webhook_public_key' => env('CHIP_PUBLIC_KEY_FOR_WEBHOOK'),
                    ],
                ],
                'senangpay' => [
                    'label'  => 'Grab PayLater',
                    'config' => [
                        'client_id'  => env('SENANGPAY_CLIENT_ID'),
                        'secret_key' => env('SENANGPAY_SECRET_KEY'),
                        'base_url'   => env('SENANGPAY_BASE_URL'),
                    ],
                ],
                'atome' => [
                    'label'  => 'Atome',
                    'bnpl'   => true,
                    'config' => [
                        'partner_id'      => env('ATOME_PARTNER_ID'),
                        'secret_key'      => env('ATOME_SECRET_KEY'),
                        'base_url'        => env('ATOME_BASE_URL'),
                        'callback_secret' => env('ATOME_CALLBACK_SECRET'),
                    ],
                ],
                'ahapay' => [
                    'label'  => 'AhaPay',
                    'bnpl'   => true,
                    'config' => [
                        'api_key'     => env('AHAPAY_API_KEY'),
                        'secret_key'  => env('AHAPAY_SECRET_KEY'),
                        'base_url'    => env('AHAPAY_BASE_URL'),
                        'merchant_id' => env('AHAPAY_MERCHANT_ID'),
                    ],
                ],
                'fiuu' => [
                    'label'  => 'Fiuu',
                    'config' => [
                        'merchant_id'         => env('FIUU_MERCHANT_ID'),
                        'verify_key'          => env('FIUU_VERIFY_KEY'),
                        'secret_key'          => env('FIUU_SECRET_KEY'),
                        'sandbox'             => env('FIUU_SANDBOX', true),
                        'vcode_with_currency' => env('FIUU_VCODE_WITH_CURRENCY', false),
                    ],
                ],
            ],
        ],

        // ── Reniu — checkout only ────────────────────────────────────────
        'reniu' => [
            'label'   => 'Reniu',
            'domains' => [
                'reniu.my',
                'www.reniu.my',
                'staging.reniu.my',
            ],

            // Only the checkout + webhooks exist on this domain; everything
            // else 404s (see EnsureRouteAllowedForSite middleware).
            'routes' => [
                'pay.create', 'pay.store', 'pay.success', 'pay.failed', 'pay.webhook',
            ],

            'reference_prefix' => 'RNU',
            'whatsapp_link'    => env('RENIU_WHATSAPP_LINK'),

            // Drop a logo at public/images/reniu-logo.png to use it; until then
            // the checkout shows the company name as text.
            'company' => 'Reniu',
            'logo'    => 'images/reniu-logo.png',

            'turnstile' => [
                'site_key'   => env('RENIU_TURNSTILE_SITE_KEY'),
                'secret_key' => env('RENIU_TURNSTILE_SECRET_KEY'),
            ],

            'gateways' => [
                // Card only on reniu — no FPX option.
                'chip' => [
                    'label'   => 'Credit Card',
                    'methods' => [
                        ['method' => 'card', 'label' => 'Kad Kredit / Debit'],
                    ],
                    'config'  => [
                        'api_key'            => env('RENIU_CHIP_API_KEY'),
                        'brand_id'           => env('RENIU_CHIP_BRAND_ID'),
                        'base_url'           => env('RENIU_CHIP_BASE_URL', 'https://gate.chip-in.asia/api/v1'),
                        'webhook_public_key' => env('RENIU_CHIP_PUBLIC_KEY_FOR_WEBHOOK'),
                    ],
                ],
                'fiuu' => [
                    'label'  => 'SPayLater',
                    'config' => [
                        'merchant_id'         => env('RENIU_FIUU_MERCHANT_ID'),
                        'verify_key'          => env('RENIU_FIUU_VERIFY_KEY'),
                        'secret_key'          => env('RENIU_FIUU_SECRET_KEY'),
                        'sandbox'             => env('RENIU_FIUU_SANDBOX', false),
                        'vcode_with_currency' => env('RENIU_FIUU_VCODE_WITH_CURRENCY', false),
                    ],
                ],
                'senangpay' => [
                    'label'  => 'Grab PayLater',
                    'config' => [
                        'client_id'  => env('RENIU_SENANGPAY_CLIENT_ID'),
                        'secret_key' => env('RENIU_SENANGPAY_SECRET_KEY'),
                        'base_url'   => env('RENIU_SENANGPAY_BASE_URL'),
                    ],
                ],
                'atome' => [
                    'label'  => 'Atome',
                    'bnpl'   => true,
                    'config' => [
                        'partner_id'      => env('RENIU_ATOME_PARTNER_ID'),
                        'secret_key'      => env('RENIU_ATOME_SECRET_KEY'),
                        'base_url'        => env('RENIU_ATOME_BASE_URL'),
                        'callback_secret' => env('RENIU_ATOME_CALLBACK_SECRET'),
                    ],
                ],
            ],
        ],
    ],
];
