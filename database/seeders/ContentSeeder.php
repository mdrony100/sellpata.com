<?php

namespace Database\Seeders;

use App\Models\Benefit;
use App\Models\Category;
use App\Models\Client;
use App\Models\Review;
use App\Models\Step;
use App\Models\Template;
use Illuminate\Database\Seeder;

/**
 * Seeds the marketing content the admin panel then edits. Running it twice is
 * safe: rows are matched on a natural key and only created when missing.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::CATEGORIES as $order => $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['id']],
                ['label' => $category['label'], 'sort_order' => $order, 'is_visible' => true],
            );
        }

        foreach (self::TEMPLATES as $order => $template) {
            $category = Category::query()->where('slug', $template['category'])->first();

            Template::query()->updateOrCreate(
                ['brand' => $template['brand']],
                [
                    'category_id' => $category?->id,
                    'headline' => $template['headline'],
                    'subline' => $template['subline'] ?? null,
                    'product_name' => $template['product'] ?? null,
                    'price' => $template['price'] ?? null,
                    'old_price' => $template['oldPrice'] ?? null,
                    'badge' => $template['badge'] ?? null,
                    'art' => $template['art'] ?? 'gift',
                    'palette' => $template['palette'] ?? null,
                    'sort_order' => $order,
                    'is_published' => true,
                ],
            );
        }

        foreach (self::BENEFITS as $order => $benefit) {
            Benefit::query()->updateOrCreate(
                ['title' => $benefit['title']],
                [
                    'body' => $benefit['body'],
                    'icon' => $benefit['icon'] ?? 'spark',
                    'feature_key' => $benefit['feature'] ?? null,
                    'sort_order' => $order,
                    'is_published' => true,
                ],
            );
        }

        foreach (self::STEPS as $order => $step) {
            Step::query()->updateOrCreate(
                ['title' => $step['title']],
                [
                    'number_label' => $step['number'],
                    'body' => $step['body'],
                    'sort_order' => $order,
                    'is_published' => true,
                ],
            );
        }

        // Real client sites and real reviews start empty on purpose: the admin
        // panel is where they are added, and the public pages show an honest
        // empty state until then.
    }

    public const CATEGORIES = [
        [
            'id' => 'fashion',
            'label' => 'ফ্যাশন ও পোশাক',
        ],
        [
            'id' => 'beauty',
            'label' => 'বিউটি ও স্কিনকেয়ার',
        ],
        [
            'id' => 'food',
            'label' => 'খাবার ও হোমমেড পণ্য',
        ],
        [
            'id' => 'gadget',
            'label' => 'গ্যাজেট ও অ্যাকসেসরিজ',
        ],
        [
            'id' => 'home',
            'label' => 'ঘর ও রান্নাঘর',
        ],
        [
            'id' => 'kids',
            'label' => 'শিশুদের পণ্য',
        ],
        [
            'id' => 'gift',
            'label' => 'গিফট ও হ্যান্ডমেড পণ্য',
        ],
    ];

    public const TEMPLATES = [
        [
            'id' => 'fashion-boutique',
            'category' => 'fashion',
            'brand' => 'রূপকথা ফ্যাশন',
            'headline' => 'নতুন কালেকশন এসেছে',
            'subline' => 'সুতি ও কটন কুর্তি—আরামদায়ক, টেকসই আর everyday ready।',
            'product' => 'সুতি কুর্তি',
            'price' => '৯৯০৳',
            'oldPrice' => '১,২৫০৳',
            'badge' => 'নতুন কালেকশন',
            'cta' => 'অর্ডার করুন',
            'art' => 'fashion',
            'palette' => [
                'bg' => '#fbf6ee',
                'surface' => '#ffffff',
                'ink' => '#22201c',
                'primary' => '#0d3b2e',
                'accent' => '#c9f24d',
                'soft' => '#e8ded0',
            ],
        ],
        [
            'id' => 'beauty-glow',
            'category' => 'beauty',
            'brand' => 'গ্লো বিউটি',
            'headline' => 'ত্বকের যত্নে হালকা ফর্মুলা',
            'subline' => 'ভিটামিন-সি সিরাম, প্রতিদিনের রুটিনে সহজ।',
            'product' => 'ভিটামিন সি সিরাম',
            'price' => '৭৫০৳',
            'oldPrice' => '৮৯০৳',
            'badge' => 'বেস্ট সেলার',
            'cta' => 'অর্ডার করুন',
            'art' => 'beauty',
            'palette' => [
                'bg' => '#fdf5f4',
                'surface' => '#ffffff',
                'ink' => '#241f22',
                'primary' => '#8c2f4c',
                'accent' => '#ffd9c7',
                'soft' => '#f6dcd6',
            ],
        ],
        [
            'id' => 'food-homemade',
            'category' => 'food',
            'brand' => 'ঘরের স্বাদ',
            'headline' => 'ঘরে বানানো, ঘরের স্বাদে',
            'subline' => 'খাঁটি মধু আর ঘি—সরাসরি গ্রাম থেকে আপনার রান্নাঘরে।',
            'product' => 'খাঁটি সুন্দরবন মধু',
            'price' => '৬৫০৳',
            'oldPrice' => '৭৮০৳',
            'badge' => '১০০% খাঁটি',
            'cta' => 'অর্ডার করুন',
            'art' => 'food',
            'palette' => [
                'bg' => '#fdf7e8',
                'surface' => '#ffffff',
                'ink' => '#241f17',
                'primary' => '#9a5b12',
                'accent' => '#ffe08a',
                'soft' => '#f2e3c4',
            ],
        ],
        [
            'id' => 'gadget-tech',
            'category' => 'gadget',
            'brand' => 'টেকবাজার',
            'headline' => 'স্মার্ট গ্যাজেট, সহজ দামে',
            'subline' => 'ওয়্যারলেস ইয়ারবাড—ব্যাটারি ২৪ ঘণ্টা, ক্লিয়ার কল।',
            'product' => 'ওয়্যারলেস ইয়ারবাড',
            'price' => '১,৪৯০৳',
            'oldPrice' => '১,৯৫০৳',
            'badge' => 'ফ্রি ডেলিভারি',
            'cta' => 'অর্ডার করুন',
            'art' => 'gadget',
            'palette' => [
                'bg' => '#f2f5f7',
                'surface' => '#ffffff',
                'ink' => '#1a2024',
                'primary' => '#144a63',
                'accent' => '#9fe6ff',
                'soft' => '#dde7ec',
            ],
        ],
        [
            'id' => 'home-kitchen',
            'category' => 'home',
            'brand' => 'রান্নাঘর',
            'headline' => 'রান্না হোক আরও সহজ',
            'subline' => 'নন-স্টিক প্যান সেট—কম তেলে, সমান আঁচে।',
            'product' => 'নন-স্টিক প্যান',
            'price' => '১,২৫০৳',
            'oldPrice' => '১,৬০০৳',
            'badge' => 'কম্বো অফার',
            'cta' => 'অর্ডার করুন',
            'art' => 'home',
            'palette' => [
                'bg' => '#f4f6f2',
                'surface' => '#ffffff',
                'ink' => '#1e241d',
                'primary' => '#2f5d3a',
                'accent' => '#d8f0a8',
                'soft' => '#dfe7d8',
            ],
        ],
        [
            'id' => 'kids-play',
            'category' => 'kids',
            'brand' => 'খেলাঘর',
            'headline' => 'খেলা হোক শেখারও',
            'subline' => 'কাঠের ব্লক সেট—নিরাপদ রঙ, ছোট হাতের জন্য উপযুক্ত।',
            'product' => 'কাঠের ব্লক সেট',
            'price' => '৮৫০৳',
            'oldPrice' => '১,০০০৳',
            'badge' => 'বয়স ৩+',
            'cta' => 'অর্ডার করুন',
            'art' => 'kids',
            'palette' => [
                'bg' => '#f5f3ff',
                'surface' => '#ffffff',
                'ink' => '#211f2b',
                'primary' => '#5b3cc4',
                'accent' => '#ffe066',
                'soft' => '#e3dcff',
            ],
        ],
        [
            'id' => 'gift-handmade',
            'category' => 'gift',
            'brand' => 'হাতের গিফট',
            'headline' => 'হাতে বানানো, মনের কথা',
            'subline' => 'কাস্টম গিফট বক্স—নাম লেখা, রিবন আর ছোট্ট নোটসহ।',
            'product' => 'কাস্টম গিফট বক্স',
            'price' => '৯৫০৳',
            'oldPrice' => '১,১৫০৳',
            'badge' => 'কাস্টমাইজযোগ্য',
            'cta' => 'অর্ডার করুন',
            'art' => 'gift',
            'palette' => [
                'bg' => '#fdf3f6',
                'surface' => '#ffffff',
                'ink' => '#241c20',
                'primary' => '#a83a5f',
                'accent' => '#ffd6e4',
                'soft' => '#f4dbe3',
            ],
        ],
    ];

    public const BENEFITS = [
        [
            'key' => 'ready',
            'title' => 'রেডিমেড ল্যান্ডিং পেজ',
            'body' => 'শুরু থেকে ডিজাইন করার ঝামেলা ছাড়াই পছন্দের ডিজাইন বেছে নিন।',
        ],
        [
            'key' => 'category',
            'title' => 'ক্যাটাগরি অনুযায়ী ডিজাইন',
            'body' => 'আপনার পণ্যের ধরন অনুযায়ী সাজানো টেমপ্লেট।',
        ],
        [
            'key' => 'custom',
            'title' => 'সহজ কাস্টমাইজেশন',
            'body' => 'লোগো, রঙ, ছবি ও লেখা বদলে নিজের ব্র্যান্ডে সাজান।',
        ],
        [
            'key' => 'payments',
            'title' => 'পেমেন্ট গেটওয়ে',
            'body' => 'অনলাইন পেমেন্ট নেওয়ার সুবিধা।',
            'feature' => 'payments',
        ],
        [
            'key' => 'shipping',
            'title' => 'শিপিং সার্ভিস',
            'body' => 'ডেলিভারি ব্যবস্থাপনার সুবিধা।',
            'feature' => 'shipping',
        ],
    ];

    public const STEPS = [
        [
            'number' => '১',
            'title' => 'ডিজাইন বেছে নিন',
            'body' => 'আপনার পণ্যের ক্যাটাগরি থেকে পছন্দের টেমপ্লেট নির্বাচন করুন।',
            'art' => 'choose',
        ],
        [
            'number' => '২',
            'title' => 'নিজের মতো সাজান',
            'body' => 'পণ্যের ছবি, দাম, বিবরণ, লোগো ও ব্র্যান্ডের রঙ যোগ করুন।',
            'art' => 'customize',
        ],
        [
            'number' => '৩',
            'title' => 'লিংক শেয়ার করুন',
            'body' => 'আপনার ল্যান্ডিং পেজের লিংক ফেসবুক পোস্ট, বিজ্ঞাপন ও মেসেঞ্জারে দিন।',
            'art' => 'share',
        ],
    ];
}
