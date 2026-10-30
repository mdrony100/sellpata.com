<?php

/*
|--------------------------------------------------------------------------
| SellPata marketing site content
|--------------------------------------------------------------------------
|
| Everything the public site needs that may change without touching Vue
| components lives here. Values are passed to the frontend once, as a JSON
| bootstrap object, so the same config drives every page.
|
*/

return [

    // Leave empty until the real tutorial exists; the hero then shows an
    // intentional Bengali placeholder instead of a fake video.
    'youtube_url' => env('SELLPATA_YOUTUBE_URL'),

    // Marketing claim shown in the template section. Keep this null until the
    // real inventory is confirmed, which shows the neutral label instead of
    // the "৫০০+ রেডিমেড ল্যান্ডিং পেজ" target.
    'template_count_label' => env('SELLPATA_TEMPLATE_COUNT_LABEL'),

    // Feature availability. Nothing is claimed as live until the integration
    // actually exists.
    'features' => [
        'payments' => [
            'available' => filter_var(env('SELLPATA_PAYMENTS_AVAILABLE', false), FILTER_VALIDATE_BOOL),
            'coming_soon_label' => 'শিগগিরই আসছে',
        ],
        'shipping' => [
            'available' => filter_var(env('SELLPATA_SHIPPING_AVAILABLE', false), FILTER_VALIDATE_BOOL),
            'coming_soon_label' => 'শিগগিরই আসছে',
        ],
    ],

    // Only rendered when a real value is supplied. No invented numbers.
    'contact' => [
        'phone' => env('SELLPATA_CONTACT_PHONE'),
        'email' => env('SELLPATA_CONTACT_EMAIL'),
        'whatsapp' => env('SELLPATA_CONTACT_WHATSAPP'),
    ],

    'brand' => [
        'name' => 'SellPata',
        'domain' => 'sellpata.com',
        'tagline' => 'ফেসবুক উদ্যোক্তাদের জন্য সহজ ব্র্যান্ডেড ল্যান্ডিং পেজ।',
    ],
];
