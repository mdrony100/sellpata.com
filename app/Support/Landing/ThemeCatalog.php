<?php

namespace App\Support\Landing;

use App\Models\BuilderTemplate;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class ThemeCatalog
{
    public const DESIGNS = ['prakritik', 'abhijatya', 'smart', 'komol', 'sorasari'];

    public const VERSION = '1.0.0';

    public static function isLanding(?string $design): bool
    {
        return in_array($design, self::DESIGNS, true);
    }

    public static function seed(): void
    {
        $schema = self::schema();

        foreach (self::definitions() as $index => $theme) {
            $categoryId = Category::where('slug', $theme['category'])->value('id');

            BuilderTemplate::updateOrCreate(
                ['slug' => $theme['slug']],
                [
                    'category_id' => $categoryId,
                    'name' => $theme['name'],
                    'design' => $theme['design'],
                    'theme_version' => self::VERSION,
                    'tagline' => $theme['tagline'],
                    'suitable_for' => $theme['suitable_for'],
                    'schema' => $schema,
                    'content' => $theme['content'],
                    'thumbnail' => '/themes/previews/'.$theme['slug'].'-thumb.jpg',
                    'preview' => '/themes/previews/'.$theme['slug'].'-full.jpg',
                    'is_published' => true,
                    'sort_order' => $index,
                ]
            );
        }

        BuilderTemplate::query()
            ->whereNotIn('design', self::DESIGNS)
            ->where('sort_order', '<', 10)
            ->update(['sort_order' => DB::raw('sort_order + 10')]);
    }

    /**
     * Keep merchant text, prices, media and policies.
     * The destination theme supplies its own hero mode and section order.
     *
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public static function mergeContent(array $current, array $defaults): array
    {
        $merged = $defaults;

        foreach (['brand', 'hero', 'product', 'cta', 'contact', 'delivery', 'policies', 'details', 'offer', 'selection', 'sections', 'headings', 'tracking'] as $key) {
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                continue;
            }
            $base = is_array($merged[$key] ?? null) ? $merged[$key] : [];
            $merged[$key] = array_replace($base, $current[$key]);
        }

        foreach (['benefits', 'features', 'gallery', 'reviews', 'variants', 'bundles', 'faq'] as $key) {
            if (isset($current[$key]) && is_array($current[$key]) && count($current[$key]) > 0) {
                $merged[$key] = $current[$key];
            }
        }

        if (empty($current['benefits']) && ! empty($current['features']) && is_array($current['features'])) {
            $merged['benefits'] = array_map(fn ($feature) => [
                'title' => $feature['title'] ?? '',
                'text' => $feature['text'] ?? '',
                'icon' => '•',
            ], $current['features']);
        }

        $destinationHasNoReviews = ($defaults['reviews'] ?? null) === [];
        if ($destinationHasNoReviews) {
            $merged['reviews'] = array_values(array_filter(
                $merged['reviews'] ?? [],
                fn ($review) => is_array($review) && empty($review['sample']) && trim((string) ($review['text'] ?? '')) !== ''
            ));
        }

        $merged['hero']['mode'] = $defaults['hero']['mode'] ?? ($merged['hero']['mode'] ?? 'split');
        $merged['section_order'] = $defaults['section_order'] ?? [];

        return self::constrain($merged);
    }

    /** @param  array<string, mixed>  $content */
    public static function constrain(array $content): array
    {
        $caps = ['benefits' => 6, 'faq' => 12, 'gallery' => 8, 'variants' => 24, 'bundles' => 6, 'features' => 6];
        foreach ($caps as $key => $max) {
            if (isset($content[$key]) && is_array($content[$key])) {
                $content[$key] = array_slice(array_values($content[$key]), 0, $max);
            }
        }

        $order = ['hero', 'benefits', 'details', 'gallery', 'selection', 'reviews', 'policies', 'faq', 'order'];
        if (isset($content['section_order']) && is_array($content['section_order'])) {
            $content['section_order'] = array_values(array_unique(array_filter(
                $content['section_order'],
                fn ($key) => in_array($key, $order, true)
            )));
            foreach (['hero', 'order'] as $required) {
                if (! in_array($required, $content['section_order'], true)) {
                    $content['section_order'][] = $required;
                }
            }
        }

        array_walk_recursive($content, function (&$value) {
            if (is_string($value)) {
                $value = trim(strip_tags($value));
                if (strlen($value) > 4000) {
                    $value = mb_substr($value, 0, 4000);
                }
            }
        });

        return $content;
    }

    /** @return list<array<string, mixed>> */
    public static function schema(): array
    {
        $sections = [
            ['value' => 'hero', 'label' => 'হিরো'],
            ['value' => 'benefits', 'label' => 'সুবিধা'],
            ['value' => 'details', 'label' => 'বিবরণ'],
            ['value' => 'gallery', 'label' => 'গ্যালারি'],
            ['value' => 'selection', 'label' => 'পছন্দ'],
            ['value' => 'reviews', 'label' => 'রিভিউ'],
            ['value' => 'policies', 'label' => 'নীতিমালা'],
            ['value' => 'faq', 'label' => 'প্রশ্নোত্তর'],
            ['value' => 'order', 'label' => 'অর্ডার ফর্ম'],
        ];

        return [
            ['key' => 'brand.name', 'label' => 'দোকানের নাম', 'type' => 'text', 'required' => true, 'group' => 'ব্র্যান্ড', 'maxlength' => 80],
            ['key' => 'brand.logo', 'label' => 'লোগো', 'type' => 'image', 'group' => 'ব্র্যান্ড'],
            ['key' => 'brand.color', 'label' => 'ব্র্যান্ডের রঙ', 'type' => 'color', 'group' => 'ব্র্যান্ড'],
            ['key' => 'brand.accent', 'label' => 'অ্যাকসেন্ট রঙ', 'type' => 'color', 'group' => 'ব্র্যান্ড'],
            ['key' => 'contact.phone', 'label' => 'যোগাযোগের নম্বর', 'type' => 'text', 'group' => 'ব্র্যান্ড', 'maxlength' => 20],
            ['key' => 'contact.verified', 'label' => 'নম্বর যাচাইকৃত', 'type' => 'toggle', 'group' => 'ব্র্যান্ড'],
            ['key' => 'contact.message', 'label' => 'ডেলিভারি বা সাপোর্টের ছোট বার্তা', 'type' => 'text', 'group' => 'ব্র্যান্ড', 'maxlength' => 120],
            ['key' => 'hero.mode', 'label' => 'হিরোর ধরন', 'type' => 'select', 'group' => 'হিরো', 'options' => [
                ['value' => 'split', 'label' => 'লেখা ৫০% + গ্যালারি ৫০%'],
                ['value' => 'video', 'label' => 'মাঝখানে শিরোনাম ও ভিডিও'],
                ['value' => 'banner', 'label' => 'মাঝখানে ব্যানার, তারপর লেখা ও বাটন'],
            ]],
            ['key' => 'hero.badge', 'label' => 'ছোট ব্যাজ', 'type' => 'text', 'group' => 'হিরো', 'maxlength' => 40],
            ['key' => 'hero.headline', 'label' => 'শিরোনাম', 'type' => 'text', 'required' => true, 'group' => 'হিরো', 'maxlength' => 160],
            ['key' => 'hero.subline', 'label' => 'সহায়ক লেখা', 'type' => 'textarea', 'group' => 'হিরো', 'maxlength' => 400],
            ['key' => 'hero.video_url', 'label' => 'ইউটিউব ভিডিওর লিংক', 'type' => 'text', 'group' => 'হিরো', 'maxlength' => 200],
            ['key' => 'product.name', 'label' => 'পণ্যের নাম', 'type' => 'text', 'required' => true, 'group' => 'পণ্য', 'maxlength' => 120],
            ['key' => 'product.price', 'label' => 'নিয়মিত দাম', 'type' => 'price', 'group' => 'পণ্য'],
            ['key' => 'product.sale_price', 'label' => 'বিক্রয় মূল্য', 'type' => 'price', 'required' => true, 'group' => 'পণ্য'],
            ['key' => 'product.description', 'label' => 'পণ্যের বিবরণ', 'type' => 'textarea', 'group' => 'পণ্য', 'maxlength' => 2000],
            ['key' => 'product.image', 'label' => 'মূল ছবি', 'type' => 'image', 'group' => 'পণ্য'],
            ['key' => 'selection.mode', 'label' => 'পছন্দের ধরন', 'type' => 'select', 'group' => 'পণ্য', 'options' => [
                ['value' => 'simple', 'label' => 'একটি পণ্য'],
                ['value' => 'variants', 'label' => 'ভ্যারিয়েন্ট'],
                ['value' => 'bundles', 'label' => 'প্যাকেজ বা বান্ডিল'],
            ]],
            ['key' => 'variants', 'label' => 'ভ্যারিয়েন্ট', 'type' => 'list', 'max' => 24, 'group' => 'পণ্য', 'item' => [
                ['key' => 'id', 'label' => 'আইডি'],
                ['key' => 'name', 'label' => 'নাম'],
                ['key' => 'color', 'label' => 'রঙের নাম'],
                ['key' => 'swatch', 'label' => 'রঙের কোড'],
                ['key' => 'size', 'label' => 'সাইজ'],
                ['key' => 'price', 'label' => 'দাম'],
                ['key' => 'regular_price', 'label' => 'নিয়মিত দাম'],
                ['key' => 'image', 'label' => 'ছবি', 'type' => 'image'],
                ['key' => 'available', 'label' => 'পাওয়া যায় (1 বা 0)'],
            ]],
            ['key' => 'bundles', 'label' => 'প্যাকেজ', 'type' => 'list', 'max' => 6, 'group' => 'পণ্য', 'item' => [
                ['key' => 'id', 'label' => 'আইডি'],
                ['key' => 'name', 'label' => 'নাম'],
                ['key' => 'detail', 'label' => 'সংক্ষেপ'],
                ['key' => 'points', 'label' => 'পয়েন্ট, কমা দিয়ে'],
                ['key' => 'price', 'label' => 'দাম'],
                ['key' => 'regular_price', 'label' => 'নিয়মিত দাম'],
                ['key' => 'image', 'label' => 'ছবি', 'type' => 'image'],
                ['key' => 'available', 'label' => 'পাওয়া যায় (1 বা 0)'],
            ]],
            ['key' => 'details.materials', 'label' => 'উপকরণ', 'type' => 'textarea', 'group' => 'বিবরণ', 'maxlength' => 800],
            ['key' => 'details.included', 'label' => 'যা থাকছে', 'type' => 'list', 'max' => 8, 'group' => 'বিবরণ', 'item' => [
                ['key' => 'text', 'label' => 'লাইন'],
            ]],
            ['key' => 'details.specs', 'label' => 'স্পেসিফিকেশন', 'type' => 'list', 'max' => 12, 'group' => 'বিবরণ', 'item' => [
                ['key' => 'label', 'label' => 'নাম'],
                ['key' => 'value', 'label' => 'মান'],
            ]],
            ['key' => 'details.ingredients', 'label' => 'উপাদান', 'type' => 'list', 'max' => 8, 'group' => 'বিবরণ', 'item' => [
                ['key' => 'name', 'label' => 'নাম'],
                ['key' => 'note', 'label' => 'টিকা'],
            ]],
            ['key' => 'details.usage', 'label' => 'ব্যবহার বা যত্ন', 'type' => 'list', 'max' => 8, 'group' => 'বিবরণ', 'item' => [
                ['key' => 'text', 'label' => 'ধাপ'],
            ]],
            ['key' => 'headings.benefits', 'label' => 'সুবিধার শিরোনাম', 'type' => 'text', 'group' => 'সুবিধা', 'maxlength' => 80],
            ['key' => 'benefits', 'label' => 'সুবিধা', 'type' => 'list', 'max' => 6, 'group' => 'সুবিধা', 'item' => [
                ['key' => 'title', 'label' => 'শিরোনাম'],
                ['key' => 'text', 'label' => 'বিবরণ'],
            ]],
            ['key' => 'headings.gallery', 'label' => 'গ্যালারির শিরোনাম', 'type' => 'text', 'group' => 'গ্যালারি', 'maxlength' => 80],
            ['key' => 'gallery', 'label' => 'গ্যালারি', 'type' => 'list', 'max' => 8, 'group' => 'গ্যালারি', 'item' => [
                ['key' => 'src', 'label' => 'ছবি', 'type' => 'image'],
                ['key' => 'alt', 'label' => 'বর্ণনা'],
                ['key' => 'ratio', 'label' => 'অনুপাত (1/1, 3/4, 4/3)'],
            ]],
            ['key' => 'headings.reviews', 'label' => 'রিভিউর শিরোনাম', 'type' => 'text', 'group' => 'রিভিউ', 'maxlength' => 80],
            ['key' => 'reviews', 'label' => 'রিভিউ', 'type' => 'list', 'max' => 8, 'group' => 'রিভিউ', 'item' => [
                ['key' => 'name', 'label' => 'নাম'],
                ['key' => 'text', 'label' => 'মতামত'],
                ['key' => 'rating', 'label' => 'রেটিং ১–৫'],
                ['key' => 'photo', 'label' => 'ছবি', 'type' => 'image'],
                ['key' => 'sample', 'label' => 'নমুনা (1 হলে প্রকাশিত পেজে লুকানো)'],
            ]],
            ['key' => 'delivery.eta', 'label' => 'সম্ভাব্য ডেলিভারি সময়', 'type' => 'text', 'group' => 'ডেলিভারি', 'maxlength' => 180],
            ['key' => 'delivery.note', 'label' => 'ডেলিভারি নোট', 'type' => 'textarea', 'group' => 'ডেলিভারি', 'maxlength' => 400],
            ['key' => 'delivery.areas', 'label' => 'ডেলিভারি এলাকা', 'type' => 'list', 'max' => 12, 'group' => 'ডেলিভারি', 'item' => [
                ['key' => 'id', 'label' => 'আইডি'],
                ['key' => 'name', 'label' => 'এলাকার নাম'],
                ['key' => 'charge', 'label' => 'চার্জ'],
            ]],
            ['key' => 'policies.returns', 'label' => 'পরিবর্তন বা ফেরতের নিয়ম', 'type' => 'textarea', 'group' => 'নীতিমালা', 'maxlength' => 800],
            ['key' => 'policies.support', 'label' => 'সাপোর্টের কথা', 'type' => 'text', 'group' => 'নীতিমালা', 'maxlength' => 180],
            ['key' => 'headings.faq', 'label' => 'প্রশ্নোত্তরের শিরোনাম', 'type' => 'text', 'group' => 'প্রশ্নোত্তর', 'maxlength' => 80],
            ['key' => 'faq', 'label' => 'প্রশ্নোত্তর', 'type' => 'list', 'max' => 12, 'group' => 'প্রশ্নোত্তর', 'item' => [
                ['key' => 'q', 'label' => 'প্রশ্ন'],
                ['key' => 'a', 'label' => 'উত্তর (বাঁধা খালি থাকলে)'],
                ['key' => 'bind', 'label' => 'বাঁধা: how_to_order, delivery_charge, delivery_areas, payment, returns'],
            ]],
            ['key' => 'cta.label', 'label' => 'বাটনের লেখা', 'type' => 'text', 'group' => 'অর্ডার', 'maxlength' => 40],
            ['key' => 'offer.active', 'label' => 'ছাড় চালু', 'type' => 'toggle', 'group' => 'অর্ডার'],
            ['key' => 'offer.amount', 'label' => 'ছাড়ের পরিমাণ (টাকা)', 'type' => 'price', 'group' => 'অর্ডার'],
            ['key' => 'offer.label', 'label' => 'ছাড়ের নাম', 'type' => 'text', 'group' => 'অর্ডার', 'maxlength' => 40],
            ['key' => 'tracking.meta_pixel', 'label' => 'মেটা পিক্সেল আইডি (পেইড প্ল্যান)', 'type' => 'text', 'group' => 'ট্র্যাকিং', 'maxlength' => 20],
            ['key' => 'tracking.ga4', 'label' => 'GA4 আইডি (পেইড প্ল্যান)', 'type' => 'text', 'group' => 'ট্র্যাকিং', 'maxlength' => 20],
            ['key' => 'section_order', 'label' => 'সেকশনের ক্রম', 'type' => 'section_order', 'group' => 'সেকশন', 'options' => $sections],
            ['key' => 'sections.benefits', 'label' => 'সুবিধা দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
            ['key' => 'sections.details', 'label' => 'বিবরণ দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
            ['key' => 'sections.gallery', 'label' => 'গ্যালারি দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
            ['key' => 'sections.selection', 'label' => 'পছন্দ দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
            ['key' => 'sections.reviews', 'label' => 'রিভিউ দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
            ['key' => 'sections.policies', 'label' => 'নীতিমালা দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
            ['key' => 'sections.faq', 'label' => 'প্রশ্নোত্তর দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function definitions(): array
    {
        $faq = self::boundFaq();
        $areas = [
            ['id' => 'dhaka', 'name' => 'ঢাকা মহানগর', 'charge' => 60],
            ['id' => 'nearby', 'name' => 'ঢাকার আশপাশ', 'charge' => 80],
            ['id' => 'outside', 'name' => 'ঢাকার বাইরে', 'charge' => 120],
        ];
        $sections = [
            'benefits' => true, 'details' => true, 'gallery' => true, 'selection' => true,
            'reviews' => true, 'policies' => true, 'faq' => true,
        ];

        return [
            self::theme('prakritik', 'প্রাকৃতিক', 'honey', ['honey', 'homemade', 'spice'], 'মধু, ঘি, খেজুর ও ঘরোয়া খাবারের জন্য নরম সবুজ পাতা', [
                'brand' => ['name' => 'বনের মধু', 'color' => '#1f4d3a', 'accent' => '#c4843c', 'logo' => null],
                'contact' => ['phone' => '', 'verified' => false, 'message' => 'এলাকা বেছে নিলে ডেলিভারি চার্জ বদলায়।'],
                'hero' => [
                    'mode' => 'split',
                    'badge' => 'নমুনা পাতা',
                    'headline' => 'সরিষা ফুলের মধু, কাঁচের বয়ামে',
                    'subline' => 'মৌসুমে সংগ্রহ করা ঘন মধু। রঙ সোনালি, স্বাদ মৃদু। কোনো অতিরিক্ত দাবি এই নমুনায় নেই।',
                    'video_url' => '',
                ],
                'product' => [
                    'name' => 'সরিষা ফুলের মধু',
                    'price' => 780,
                    'sale_price' => 690,
                    'description' => '৫০০ গ্রামের কাঁচের বয়াম। মধু ঘন, রঙ সোনালি। ঠান্ডা ও শুকনো জায়গায় রাখলে ভালো থাকে। এটি একটি নমুনা বিবরণ — প্রকাশের আগে নিজের পণ্যের কথা বসান।',
                    'image' => '/themes/prakritik/jar.jpg',
                ],
                'selection' => ['mode' => 'variants'],
                'variants' => [
                    ['id' => '250', 'name' => '২৫০ গ্রাম', 'attribute' => 'ওজন', 'size' => '২৫০ গ্রাম', 'price' => 390, 'regular_price' => 450, 'image' => '/themes/prakritik/jar.jpg', 'available' => true],
                    ['id' => '500', 'name' => '৫০০ গ্রাম', 'attribute' => 'ওজন', 'size' => '৫০০ গ্রাম', 'price' => 690, 'regular_price' => 780, 'image' => '/themes/prakritik/dipper.jpg', 'available' => true],
                ],
                'bundles' => [],
                'details' => [
                    'materials' => '',
                    'included' => [
                        ['text' => 'কাঁচের বয়াম'],
                        ['text' => 'সরিষা ফুলের মধু, লেবেলে লেখা ওজন'],
                    ],
                    'specs' => [
                        ['label' => 'ধরন', 'value' => 'সরিষা ফুলের মধু'],
                        ['label' => 'রাখার জায়গা', 'value' => 'ঠান্ডা ও শুকনো'],
                    ],
                    'ingredients' => [],
                    'usage' => [
                        ['text' => 'শুকনো চামচ দিয়ে নিন।'],
                        ['text' => 'ঢাকনা বন্ধ করে রাখুন।'],
                    ],
                ],
                'headings' => self::headings('কেন এই মধুটি রাখবেন?', 'বয়াম ও খেজুর', 'নমুনা মতামত'),
                'benefits' => [
                    ['title' => 'ঘন গড়ন', 'text' => 'বয়ামে ঢাললে ধীরে নামে।'],
                    ['title' => 'কাঁচের পাত্র', 'text' => 'মধু দেখা যায়, পাত্র ধোয়া যায়।'],
                    ['title' => 'ওজন লেখা', 'text' => '২৫০ বা ৫০০ গ্রাম — যা বেছে নেবেন।'],
                    ['title' => 'সহজ রাখা', 'text' => 'শুকনো জায়গায় রাখার কথা লেবেলে থাকে।'],
                ],
                'gallery' => [
                    ['src' => '/themes/prakritik/jar.jpg', 'alt' => 'কাঁচের বয়ামে সোনালি মধু', 'ratio' => '1/1'],
                    ['src' => '/themes/prakritik/dipper.jpg', 'alt' => 'কাঠের চামচে মধু', 'ratio' => '4/3'],
                    ['src' => '/themes/prakritik/dates.jpg', 'alt' => 'খেজুর ও হলুদ গুঁড়া', 'ratio' => '1/1'],
                ],
                'reviews' => self::sampleReviews([
                    ['name' => 'নমুনা ক্রেতা', 'text' => 'বয়াম অক্ষত এসেছে। মধুর রঙ সোনালি।', 'rating' => 5],
                    ['name' => 'আরেকটি নমুনা', 'text' => '৫০০ গ্রামের বয়ামটি চাইলে সহজে বেছে নেওয়া যায়।', 'rating' => 4],
                ]),
                'delivery' => ['eta' => 'ঢাকায় সাধারণত ১–৩ কার্যদিবস, বাইরে ৩–৫ কার্যদিবস। নিশ্চিত সময় ডেলিভারির দিন জানানো হয়।', 'note' => 'চার্জ নির্বাচিত এলাকা অনুযায়ী।', 'areas' => $areas],
                'policies' => ['returns' => '', 'support' => ''],
                'faq' => $faq,
                'cta' => ['label' => 'অর্ডার করুন'],
                'offer' => ['active' => false, 'amount' => 0, 'label' => ''],
                'sections' => $sections,
                'section_order' => ['hero', 'benefits', 'details', 'gallery', 'selection', 'reviews', 'policies', 'faq', 'order'],
                'tracking' => ['meta_pixel' => '', 'ga4' => ''],
            ]),
            self::theme('abhijatya', 'আভিজাত্য', 'shari', ['shari', 'poshak', 'bag', 'goyna'], 'শাড়ি ও পোশাকের জন্য সম্পাদকীয়, ফাঁকা জায়গা ও মিহি বর্ডার', [
                'brand' => ['name' => 'নকশি ঘর', 'color' => '#2a2424', 'accent' => '#7a3040', 'logo' => null],
                'contact' => ['phone' => '', 'verified' => false, 'message' => 'রঙ ও হাতের মাপ বেছে নিন।'],
                'hero' => [
                    'mode' => 'banner',
                    'badge' => 'হাতের তাঁত',
                    'headline' => 'সুতির শাড়ি, শান্ত বর্ডার',
                    'subline' => 'হাতের তাঁতে বোনা সুতি। প্রতিদিনের জন্য হালকা গড়ন।',
                    'video_url' => '',
                ],
                'product' => [
                    'name' => 'সুতি শাড়ি',
                    'price' => 2800,
                    'sale_price' => 2450,
                    'description' => 'সুতি শাড়ি, সরু বোনা বর্ডার। ব্লাউজ পিস আলাদা করে ভাঁজ করা থাকে। রঙ ও হাতের মাপ নিচ থেকে বেছে নিন।',
                    'image' => '/themes/abhijatya/saree-red.jpg',
                ],
                'selection' => ['mode' => 'variants'],
                'variants' => [
                    ['id' => 'lal-12', 'name' => 'লাল · ১২ হাত', 'color' => 'লাল', 'swatch' => '#7a3040', 'size' => '১২ হাত', 'price' => 2450, 'regular_price' => 2800, 'image' => '/themes/abhijatya/saree-red.jpg', 'available' => true],
                    ['id' => 'lal-13', 'name' => 'লাল · ১৩ হাত', 'color' => 'লাল', 'swatch' => '#7a3040', 'size' => '১৩ হাত', 'price' => 2650, 'regular_price' => 3000, 'image' => '/themes/abhijatya/saree-red.jpg', 'available' => true],
                    ['id' => 'nil-12', 'name' => 'নীল · ১২ হাত', 'color' => 'নীল', 'swatch' => '#2c3e6b', 'size' => '১২ হাত', 'price' => 2450, 'regular_price' => 2800, 'image' => '/themes/abhijatya/saree-blue.jpg', 'available' => true],
                    ['id' => 'nil-13', 'name' => 'নীল · ১৩ হাত', 'color' => 'নীল', 'swatch' => '#2c3e6b', 'size' => '১৩ হাত', 'price' => 2650, 'regular_price' => 3000, 'image' => '/themes/abhijatya/saree-blue.jpg', 'available' => false],
                ],
                'bundles' => [],
                'details' => [
                    'materials' => 'সুতি সুতা, হাতের তাঁত। বর্ডার একই কাপড়ে বোনা।',
                    'included' => [
                        ['text' => 'শাড়ি'],
                        ['text' => 'ব্লাউজ পিস'],
                    ],
                    'specs' => [
                        ['label' => 'কাপড়', 'value' => 'সুতি'],
                        ['label' => 'বর্ডার', 'value' => 'সরু, বোনা'],
                    ],
                    'ingredients' => [],
                    'usage' => [
                        ['text' => 'প্রথমবার আলাদা করে ঠান্ডা পানিতে ধুয়ে নিন।'],
                        ['text' => 'রোদে সরাসরি না শুকিয়ে ছায়ায় মেলে দিন।'],
                    ],
                ],
                'headings' => self::headings('শাড়িটি যে কারণে আলাদা', 'লুকবুক', 'পরা মানুষের কথা'),
                'benefits' => [
                    ['title' => 'হালকা সুতি', 'text' => 'গরমের দিনে পরতে আরামদায়ক গড়ন।'],
                    ['title' => 'দুই রঙ', 'text' => 'লাল ও নীল — যেটি মিলবে সেটি নিন।'],
                    ['title' => 'মাপ আলাদা', 'text' => '১২ হাত ও ১৩ হাত, যেটি নেই তা বন্ধ থাকে।'],
                    ['title' => 'ব্লাউজ পিস', 'text' => 'শাড়ির সঙ্গে ভাঁজ করা থাকে।'],
                ],
                'gallery' => [
                    ['src' => '/themes/abhijatya/saree-red.jpg', 'alt' => 'লাল সুতির শাড়ি ভাঁজ করা', 'ratio' => '3/4'],
                    ['src' => '/themes/abhijatya/border.jpg', 'alt' => 'শাড়ির বর্ডারের কাছের ছবি', 'ratio' => '1/1'],
                    ['src' => '/themes/abhijatya/saree-blue.jpg', 'alt' => 'নীল সুতির শাড়ি', 'ratio' => '3/4'],
                ],
                'reviews' => self::sampleReviews([
                    ['name' => 'নমুনা ক্রেতা', 'text' => 'কাপড় হালকা। বর্ডার ছবিতে যেমন, হাতেও তেমন।', 'rating' => 5],
                    ['name' => 'নমুনা নোট', 'text' => '১২ হাত আমার জন্য যথেষ্ট হয়েছে।', 'rating' => 4],
                ]),
                'delivery' => ['eta' => 'ভাঁজ করে পাঠানো হয়। ঢাকায় সাধারণত ২–৪ কার্যদিবস।', 'note' => 'চার্জ এলাকা অনুযায়ী, কুরিয়ার কোম্পানি আলাদা।', 'areas' => $areas],
                'policies' => ['returns' => 'মাপ বা রঙ না মিললে গ্রহণের দিন জানালে বদলের কথা বিক্রেতা এখানে লিখতে পারেন। খালি থাকলে কোনো প্রতিশ্রুতি ধরা হয় না।', 'support' => ''],
                'faq' => $faq,
                'cta' => ['label' => 'অর্ডার করুন'],
                'offer' => ['active' => false, 'amount' => 0, 'label' => ''],
                'sections' => $sections,
                'section_order' => ['hero', 'gallery', 'benefits', 'selection', 'details', 'reviews', 'policies', 'faq', 'order'],
                'tracking' => ['meta_pixel' => '', 'ga4' => ''],
            ]),
            self::theme('smart', 'স্মার্ট', 'kitchen', ['kitchen', 'gadget', 'daily'], 'গ্যাজেট ও রান্নাঘরের সরঞ্জামের জন্য স্পষ্ট সারি ও তুলনা কার্ড', [
                'brand' => ['name' => 'কাজের সরঞ্জাম', 'color' => '#10233f', 'accent' => '#1d6fe8', 'logo' => null],
                'contact' => ['phone' => '', 'verified' => false, 'message' => 'প্যাকেজ বদলালে দামের সারাংশ বদলায়।'],
                'hero' => [
                    'mode' => 'video',
                    'badge' => 'রান্নাঘর',
                    'headline' => 'হাতে চাপা সবজি কাটার যন্ত্র',
                    'subline' => 'স্টেইনলেস ব্লেড ও খাদ্যোপযোগী পাত্র। নিচে ব্যবহারের ধাপ ও প্যাকেজের তুলনা।',
                    'video_url' => '',
                ],
                'product' => [
                    'name' => 'ম্যানুয়াল ভেজিটেবল চপার',
                    'price' => 990,
                    'sale_price' => 890,
                    'description' => 'ঢাকনা চেপে সবজি কাটা যায়। ব্লেড আলাদা করে ধুতে হয়। বিদ্যুৎ লাগে না।',
                    'image' => '/themes/smart/chopper.jpg',
                ],
                'selection' => ['mode' => 'bundles'],
                'variants' => [],
                'bundles' => [
                    ['id' => 'basic', 'name' => 'বেসিক', 'detail' => 'চপার ও একটি ব্লেড', 'points' => '১টি ব্লেড, হাতে ধোয়া যায়', 'price' => 890, 'regular_price' => 990, 'image' => '/themes/smart/chopper.jpg', 'available' => true],
                    ['id' => 'plus', 'name' => 'প্লাস', 'detail' => 'তিন ব্লেড ও ব্রাশ', 'points' => '৩টি ব্লেড, ক্লিনিং ব্রাশ', 'price' => 1290, 'regular_price' => 1490, 'image' => '/themes/smart/parts.jpg', 'available' => true],
                    ['id' => 'family', 'name' => 'ফ্যামিলি', 'detail' => 'দুই চপার ও অতিরিক্ত পাত্র', 'points' => '২টি চপার, অতিরিক্ত পাত্র', 'price' => 1890, 'regular_price' => 2200, 'image' => '/themes/smart/chopper.jpg', 'available' => true],
                ],
                'details' => [
                    'materials' => 'খাদ্যোপযোগী প্লাস্টিক বডি, স্টেইনলেস স্টিল ব্লেড।',
                    'included' => [],
                    'specs' => [
                        ['label' => 'চালানো', 'value' => 'হাতে চাপ, বিদ্যুৎ নয়'],
                        ['label' => 'ব্লেড', 'value' => 'স্টেইনলেস স্টিল, আলাদা করা যায়'],
                        ['label' => 'ধোয়া', 'value' => 'ব্লেড খুলে হাতে ধুতে হয়'],
                        ['label' => 'সার্ভিস', 'value' => 'রসিদ থাকলে বিক্রেতার লেখা নিয়ম অনুযায়ী'],
                    ],
                    'ingredients' => [],
                    'usage' => [
                        ['text' => 'সবজি ধুয়ে টুকরো করে পাত্রে দিন।'],
                        ['text' => 'ঢাকনা বন্ধ করে হাতল কয়েকবার চাপুন।'],
                        ['text' => 'ব্লেড খুলে সাবধানে ধুয়ে শুকিয়ে রাখুন।'],
                    ],
                ],
                'headings' => [
                    'benefits' => 'কেন এই সরঞ্জামটি',
                    'gallery' => 'যন্ত্র ও ব্লেড',
                    'reviews' => 'নমুনা মতামত',
                    'details' => 'স্পেসিফিকেশন',
                    'faq' => 'সচরাচর প্রশ্ন',
                    'policies' => 'ডেলিভারি ও নীতিমালা',
                ],
                'benefits' => [
                    ['title' => 'বিদ্যুৎ লাগে না', 'text' => 'হাতে চাপ দিয়েই কাটে।'],
                    ['title' => 'ব্লেড আলাদা', 'text' => 'ধোয়ার সময় ব্লেড খোলা যায়।'],
                    ['title' => 'তিন প্যাকেজ', 'text' => 'বেসিক, প্লাস বা ফ্যামিলি — দাম আলাদা।'],
                    ['title' => 'স্পষ্ট সারি', 'text' => 'উপাদান ও ধোয়ার নিয়ম সারিতে লেখা।'],
                ],
                'gallery' => [
                    ['src' => '/themes/smart/chopper.jpg', 'alt' => 'হাতে চাপা সবজি কাটার যন্ত্র', 'ratio' => '1/1'],
                    ['src' => '/themes/smart/parts.jpg', 'alt' => 'ব্লেড ও ব্রাশ', 'ratio' => '4/3'],
                ],
                'reviews' => self::sampleReviews([
                    ['name' => 'নমুনা ক্রেতা', 'text' => 'পাত্র হালকা। ব্লেড আলাদা করে ধুয়েছি।', 'rating' => 4],
                ]),
                'delivery' => ['eta' => 'বাক্স বন্ধ করে পাঠানো হয়। সময় এলাকা অনুযায়ী।', 'note' => 'কুরিয়ার বাছাই এই পাতায় নেই। চার্জ শুধু এলাকার।', 'areas' => $areas],
                'policies' => ['returns' => '', 'support' => ''],
                'faq' => $faq,
                'cta' => ['label' => 'অর্ডার করুন'],
                'offer' => ['active' => false, 'amount' => 0, 'label' => ''],
                'sections' => $sections,
                'section_order' => ['hero', 'details', 'selection', 'benefits', 'gallery', 'policies', 'faq', 'reviews', 'order'],
                'tracking' => ['meta_pixel' => '', 'ga4' => ''],
            ]),
            self::theme('komol', 'কোমল', 'haircare', ['haircare', 'skincare', 'cosmetics'], 'চুল ও ত্বকের যত্নের জন্য নরম গোল প্যানেল, ফাঁকা জায়গা', [
                'brand' => ['name' => 'ফুলের স্পর্শ', 'color' => '#6e3d55', 'accent' => '#e7c3cf', 'logo' => null],
                'contact' => ['phone' => '', 'verified' => false, 'message' => 'তেলের পরিমাণ বেছে নিন।'],
                'hero' => [
                    'mode' => 'split',
                    'badge' => 'চুলের যত্ন',
                    'headline' => 'নারকেল তেল ও গোলাপজল',
                    'subline' => 'হালকা সুগন্ধের তেল। প্রতিদিনের যত্নের জন্য অল্প পরিমাণ। ফলের প্রতিশ্রুতি এই নমুনায় নেই।',
                    'video_url' => '',
                ],
                'product' => [
                    'name' => 'নারকেল ও গোলাপ তেল',
                    'price' => 520,
                    'sale_price' => 460,
                    'description' => 'নারকেল তেলের সঙ্গে গোলাপজল মেশানো। সুগন্ধ হালকা। বোতলের মাপ ৫০ বা ১০০ মিলি।',
                    'image' => '/themes/komol/bottle.jpg',
                ],
                'selection' => ['mode' => 'variants'],
                'variants' => [
                    ['id' => '50', 'name' => '৫০ মিলি', 'size' => '৫০ মিলি', 'price' => 280, 'regular_price' => 320, 'image' => '/themes/komol/bottle.jpg', 'available' => true],
                    ['id' => '100', 'name' => '১০০ মিলি', 'size' => '১০০ মিলি', 'price' => 460, 'regular_price' => 520, 'image' => '/themes/komol/rose.jpg', 'available' => true],
                ],
                'bundles' => [],
                'details' => [
                    'materials' => '',
                    'included' => [
                        ['text' => 'কাঁচের বোতল'],
                        ['text' => 'লেবেলে লেখা মাপ'],
                    ],
                    'specs' => [],
                    'ingredients' => [
                        ['name' => 'নারকেল তেল', 'note' => 'মূল তেল'],
                        ['name' => 'গোলাপজল', 'note' => 'হালকা সুগন্ধ'],
                        ['name' => 'ভিটামিন ই', 'note' => 'তেলে মেশানো'],
                    ],
                    'usage' => [
                        ['text' => 'শুকনো চুলে অল্প তেল মাখুন।'],
                        ['text' => 'আধা ঘণ্টা পর নিজের শ্যাম্পু দিয়ে ধুয়ে নিন।'],
                        ['text' => 'চোখে লাগলে পানি দিয়ে ধুয়ে ফেলুন।'],
                    ],
                ],
                'headings' => self::headings('বোতলটিতে যা আছে', 'তেল ও গোলাপ', 'নমুনা মতামত'),
                'benefits' => [
                    ['title' => 'হালকা সুগন্ধ', 'text' => 'গোলাপজলের গন্ধ, কড়া নয়।'],
                    ['title' => 'দুই মাপ', 'text' => '৫০ মিলি বা ১০০ মিলি।'],
                    ['title' => 'সহজ যত্ন', 'text' => 'অল্প পরিমাণ মাখিয়ে শ্যাম্পু করুন।'],
                    ['title' => 'খালি দাবি নয়', 'text' => 'কোনো নিশ্চিত ফল বা আগে-পরে ছবি নেই।'],
                ],
                'gallery' => [
                    ['src' => '/themes/komol/bottle.jpg', 'alt' => 'নারকেল তেলের বোতল', 'ratio' => '1/1'],
                    ['src' => '/themes/komol/rose.jpg', 'alt' => 'গোলাপজল ও তেল', 'ratio' => '1/1'],
                ],
                'reviews' => [
                    ['name' => 'নমুনা ক্রেতা', 'text' => 'সুগন্ধ হালকা। বোতল ছোট, ব্যাগে রাখা যায়।', 'rating' => 5, 'photo' => '/themes/komol/bottle.jpg', 'sample' => true],
                    ['name' => 'নমুনা নোট', 'text' => '১০০ মিলি আমার জন্য যথেষ্ট।', 'rating' => 4, 'photo' => '', 'sample' => true],
                ],
                'delivery' => ['eta' => 'বোতল মুড়ে পাঠানো হয়। সময় এলাকা অনুযায়ী।', 'note' => '', 'areas' => $areas],
                'policies' => ['returns' => '', 'support' => ''],
                'faq' => $faq,
                'cta' => ['label' => 'অর্ডার করুন'],
                'offer' => ['active' => false, 'amount' => 0, 'label' => ''],
                'sections' => $sections,
                'section_order' => ['hero', 'details', 'gallery', 'benefits', 'selection', 'reviews', 'policies', 'faq', 'order'],
                'tracking' => ['meta_pixel' => '', 'ga4' => ''],
            ]),
            self::theme('sorasari', 'সরাসরি', 'daily', ['daily', 'kitchen', 'homemade'], 'এক পণ্যের বিজ্ঞাপন পাতা: বড় দাম, কমপ্যাক্ট হেডার, নিচে অর্ডার', [
                'brand' => ['name' => 'ঘরের বোতল', 'color' => '#242220', 'accent' => '#e4572e', 'logo' => null],
                'contact' => ['phone' => '', 'verified' => false, 'message' => 'এক বোতল বা জোড়া — নিচ থেকে বেছে নিন।'],
                'hero' => [
                    'mode' => 'banner',
                    'badge' => 'এক পণ্য',
                    'headline' => 'স্টিলের পানির বোতল, ৭৫০ মিলি',
                    'subline' => 'ডাবল-ওয়াল স্টিল। ঢাকনা স্ক্রু করা। এক বোতল, জোড়া, বা চারটির সেট।',
                    'video_url' => '',
                ],
                'product' => [
                    'name' => 'স্টিলের পানির বোতল',
                    'price' => 990,
                    'sale_price' => 890,
                    'description' => '৭৫০ মিলির ডাবল-ওয়াল স্টিল বোতল। ঢাকনা ঘুরিয়ে বন্ধ হয়। গরম বা ঠান্ডা রাখার সময় বোতলের গায়ে লেখা থাকে না — এই নমুনায় সময়ের প্রতিশ্রুতি দেওয়া নেই।',
                    'image' => '/themes/sorasari/one.jpg',
                ],
                'selection' => ['mode' => 'bundles'],
                'variants' => [],
                'bundles' => [
                    ['id' => 'one', 'name' => '১টি বোতল', 'detail' => '৭৫০ মিলি', 'points' => 'একটি বোতল, স্ক্রু ঢাকনা', 'price' => 890, 'regular_price' => 990, 'image' => '/themes/sorasari/one.jpg', 'available' => true],
                    ['id' => 'pair', 'name' => '২টির জোড়া', 'detail' => 'একই মাপের দুই বোতল', 'points' => 'দুই বোতল', 'price' => 1650, 'regular_price' => 1980, 'image' => '/themes/sorasari/pair.jpg', 'available' => true],
                    ['id' => 'family', 'name' => '৪টির সেট', 'detail' => 'পরিবারের জন্য চার বোতল', 'points' => 'চার বোতল', 'price' => 3100, 'regular_price' => 3960, 'image' => '/themes/sorasari/pair.jpg', 'available' => true],
                ],
                'details' => [
                    'materials' => 'স্টেইনলেস স্টিল, ডাবল ওয়াল। বাইরের রং ম্যাট।',
                    'included' => [
                        ['text' => 'বেছে নেওয়া সংখ্যার বোতল'],
                        ['text' => 'প্রতিটিতে স্ক্রু ঢাকনা'],
                    ],
                    'specs' => [
                        ['label' => 'ধারণ', 'value' => '৭৫০ মিলি'],
                        ['label' => 'ঢাকনা', 'value' => 'স্ক্রু'],
                        ['label' => 'ধোয়া', 'value' => 'হালকা সাবান ও নরম ব্রাশ'],
                    ],
                    'ingredients' => [],
                    'usage' => [
                        ['text' => 'প্রথমবার গরম পানি দিয়ে ধুয়ে নিন।'],
                        ['text' => 'ঢাকনা শুকিয়ে আবার লাগান।'],
                    ],
                ],
                'headings' => [
                    'benefits' => 'বোতলটি যা পারে',
                    'gallery' => 'বড় করে দেখুন',
                    'reviews' => 'রিভিউ',
                    'details' => 'যা থাকছে',
                    'faq' => 'সচরাচর প্রশ্ন',
                    'policies' => 'ডেলিভারি ও পেমেন্ট',
                ],
                'benefits' => [
                    ['title' => 'এক হাতে ধরা যায়', 'text' => '৭৫০ মিলি, সরু গড়ন।'],
                    ['title' => 'স্ক্রু ঢাকনা', 'text' => 'ঘুরিয়ে বন্ধ হয়।'],
                    ['title' => 'তিন সেট', 'text' => '১, ২ বা ৪টি — দাম সেট অনুযায়ী।'],
                    ['title' => 'সরাসরি অর্ডার', 'text' => 'ফর্ম পাতার নিচে, দাম সঙ্গে সঙ্গে।'],
                ],
                'gallery' => [
                    ['src' => '/themes/sorasari/one.jpg', 'alt' => 'একটি স্টিলের পানির বোতল', 'ratio' => '1/1'],
                    ['src' => '/themes/sorasari/pair.jpg', 'alt' => 'দুটি স্টিলের পানির বোতল', 'ratio' => '4/3'],
                ],
                'reviews' => [],
                'delivery' => ['eta' => '', 'note' => 'ডেলিভারি চার্জ শুধু যে এলাকা বেছে নেবেন। কুরিয়ার কোম্পানি এই পাতায় বেছে নেওয়া যায় না।', 'areas' => $areas],
                'policies' => ['returns' => '', 'support' => ''],
                'faq' => $faq,
                'cta' => ['label' => 'অর্ডার করুন'],
                'offer' => ['active' => false, 'amount' => 0, 'label' => ''],
                'sections' => $sections,
                'section_order' => ['hero', 'benefits', 'gallery', 'selection', 'details', 'policies', 'faq', 'order'],
                'tracking' => ['meta_pixel' => '', 'ga4' => ''],
            ]),
        ];
    }

    /** @param  array<string, mixed>  $content */
    private static function theme(string $slug, string $name, string $category, array $suitable, string $tagline, array $content): array
    {
        return compact('slug', 'name', 'category', 'suitable', 'tagline') + [
            'design' => $slug,
            'suitable_for' => $suitable,
            'content' => $content,
        ];
    }

    /** @return array<string, string> */
    private static function headings(string $benefits, string $gallery, string $reviews): array
    {
        return [
            'benefits' => $benefits,
            'gallery' => $gallery,
            'reviews' => $reviews,
            'details' => 'পণ্যের বিবরণ',
            'faq' => 'সচরাচর প্রশ্ন',
            'policies' => 'ডেলিভারি ও নীতিমালা',
        ];
    }

    /** @param  list<array{name: string, text: string, rating: int}>  $rows */
    private static function sampleReviews(array $rows): array
    {
        return array_map(fn ($row) => $row + ['photo' => '', 'sample' => true], $rows);
    }

    /** @return list<array{q: string, a: string, bind: string}> */
    private static function boundFaq(): array
    {
        return [
            ['q' => 'কীভাবে অর্ডার করব?', 'a' => '', 'bind' => 'how_to_order'],
            ['q' => 'ডেলিভারি চার্জ কত?', 'a' => '', 'bind' => 'delivery_charge'],
            ['q' => 'কোন এলাকায় ডেলিভারি হয়?', 'a' => '', 'bind' => 'delivery_areas'],
            ['q' => 'কীভাবে পেমেন্ট করব?', 'a' => '', 'bind' => 'payment'],
            ['q' => 'পণ্য পরিবর্তন বা ফেরতের নিয়ম কী?', 'a' => '', 'bind' => 'returns'],
        ];
    }
}
