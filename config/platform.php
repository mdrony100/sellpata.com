<?php

return [
    'base_domain' => env('PLATFORM_BASE_DOMAIN', 'sellpata.com'),

    // A-record target shown when a customer connects their own domain.
    'server_ip' => env('PLATFORM_SERVER_IP', '169.58.90.98'),

    'username' => [
        'min' => (int) env('STORE_USERNAME_MIN', 3),
        'max' => (int) env('STORE_USERNAME_MAX', 30),
    ],

    // Infrastructure names that can never become a store address.
    'reserved_usernames' => [
        'www', 'admin', 'api', 'app', 'dashboard', 'support', 'mail', 'email', 'smtp', 'ftp',
        'ns1', 'ns2', 'cdn', 'static', 'assets', 'media', 'blog', 'help', 'status', 'docs',
        'billing', 'pay', 'payments', 'checkout', 'shop', 'stores', 'login', 'logout', 'signup',
        'register', 'start', 'builder', 'demo', 'clients', 'contact', 'about', 'terms', 'privacy',
        'sellpata', 'test', 'dev', 'staging', 'prod', 'preview',
    ],

    // Paths that can never be used as a landing-page slug.
    'reserved_slugs' => [
        'admin', 'api', 'dashboard', 'login', 'logout', 'builder', 'start', 'order', 'orders',
        'invoice', 'invoices', 'cart', 'checkout', 'assets', 'build', 'storage', 'fonts', 'sitemap',
        'robots', 's', 'cdn',
    ],

    'impersonation_ttl_minutes' => (int) env('IMPERSONATION_TTL_MINUTES', 30),

    'invoice' => [
        'issuer' => [
            'name' => env('INVOICE_ISSUER_NAME', 'SellPata'),
            'address' => env('INVOICE_ISSUER_ADDRESS', ''),
            'phone' => env('INVOICE_ISSUER_PHONE', ''),
            'email' => env('INVOICE_ISSUER_EMAIL', ''),
        ],
        // Set this only when the seller's legal/tax details are actually configured.
        'vat_note' => env('INVOICE_VAT_NOTE'),
    ],
];
