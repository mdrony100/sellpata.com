<?php

namespace App\Support;

use App\Models\Benefit;
use App\Models\Category;
use App\Models\Client;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Step;
use App\Models\Template;
use Illuminate\Database\QueryException;

/**
 * Builds the JSON object the Blade shell hands to the Vue app. Every public
 * section reads from here, so the admin panel fully controls the marketing
 * site without a deploy.
 */
class SiteContent
{
    public static function bootstrap(): array
    {
        return array_merge(self::settings(), [
            'routes' => [
                'home' => route('home'),
                'clients' => route('clients'),
                'contact' => route('contact'),
                'contact_endpoint' => route('api.contact.store'),
            ],
            'content' => self::content(),
        ]);
    }

    private static function settings(): array
    {
        try {
            return [
            'brand' => [
                'name' => Setting::get('brand.name'),
                'domain' => Setting::get('brand.domain'),
                'tagline' => Setting::get('brand.tagline'),
            ],
            'youtube_url' => (string) Setting::get('youtube_url'),
            'template_count_label' => (string) Setting::get('template_count_label'),
            'features' => [
                'payments' => [
                    'available' => Setting::bool('features.payments.available'),
                    'coming_soon_label' => Setting::get('features.coming_soon_label'),
                ],
                'shipping' => [
                    'available' => Setting::bool('features.shipping.available'),
                    'coming_soon_label' => Setting::get('features.coming_soon_label'),
                ],
            ],
            'contact' => [
                'phone' => (string) Setting::get('contact.phone'),
                'email' => (string) Setting::get('contact.email'),
                'whatsapp' => (string) Setting::get('contact.whatsapp'),
            ],
            ];
        } catch (QueryException) {
            // Before the first migrate the settings table does not exist yet;
            // the site still renders with the shipped defaults.
            return self::settingsDefaults();
        }
    }

    private static function settingsDefaults(): array
    {
        return [
            'brand' => [
                'name' => 'SellPata',
                'domain' => 'sellpata.com',
                'tagline' => 'ফেসবুক উদ্যোক্তাদের জন্য সহজ ব্র্যান্ডেড ল্যান্ডিং পেজ।',
            ],
            'youtube_url' => '',
            'template_count_label' => '',
            'features' => [
                'payments' => ['available' => false, 'coming_soon_label' => 'শিগগিরই আসছে'],
                'shipping' => ['available' => false, 'coming_soon_label' => 'শিগগিরই আসছে'],
            ],
            'contact' => ['phone' => '', 'email' => '', 'whatsapp' => ''],
        ];
    }

    private static function content(): array
    {
        try {
            return self::contentFromDatabase();
        } catch (QueryException) {
            return [
                'categories' => [], 'templates' => [], 'benefits' => [],
                'steps' => [], 'clients' => [], 'reviews' => [],
            ];
        }
    }

    private static function contentFromDatabase(): array
    {
        $categories = Category::query()->where('is_visible', true)->orderBy('sort_order')->get();

        return [
            'categories' => $categories->map(fn (Category $c) => [
                'slug' => $c->slug,
                'label' => $c->label,
            ])->values(),

            'templates' => Template::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Template $t) => [
                    'id' => 'tpl-'.$t->id,
                    'category' => $t->category?->slug,
                    'categoryLabel' => $t->category?->label,
                    'brand' => $t->brand,
                    'headline' => $t->headline,
                    'subline' => $t->subline,
                    'product' => $t->product_name,
                    'price' => $t->price,
                    'oldPrice' => $t->old_price,
                    'badge' => $t->badge,
                    'art' => $t->art,
                    'palette' => $t->palette,
                ])->values(),

            'benefits' => Benefit::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Benefit $b) => [
                    'title' => $b->title,
                    'body' => $b->body,
                    'icon' => $b->icon,
                    'feature' => $b->feature_key,
                ])->values(),

            'steps' => Step::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Step $s) => [
                    'number' => $s->number_label,
                    'title' => $s->title,
                    'body' => $s->body,
                ])->values(),

            'clients' => Client::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Client $c) => [
                    'brand' => $c->brand,
                    'category' => $c->category,
                    'url' => $c->url,
                    'image' => $c->image_path ? asset($c->image_path) : null,
                ])->values(),

            'reviews' => Review::query()
                ->where('is_published', true)
                ->latest()
                ->get()
                ->map(fn (Review $r) => [
                    'author' => $r->author,
                    'brand' => $r->brand,
                    'body' => $r->body,
                    'rating' => $r->rating,
                ])->values(),
        ];
    }
}