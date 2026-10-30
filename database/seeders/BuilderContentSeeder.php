<?php

namespace Database\Seeders;

use App\Models\BuilderTemplate;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BuilderContentSeeder extends Seeder
{
    public function run(): void
    {
        $content0 = [
                'brand' => ['name' => 'রুপকথা বুটিক', 'color' => '#0d3b2e', 'accent' => '#c9f24d', 'logo' => null],
                'hero' => ['badge' => 'নতুন কালেকশন এসেছে', 'headline' => 'আরামদায়ক সুতি কুর্তি, এখন আপনার দামেই', 'subline' => 'হাতে বাছাই করা সুতি কাপড়, আরামদায়ক কাট—প্রতিদিন পরার জন্য।'],
                'product' => ['name' => 'সুতি কুর্তি', 'price' => '১২৫০', 'sale_price' => '৯৯০', 'description' => 'মিষ্টি গোলাপি ও অফ-হোয়াইট—দুই রঙে পাওয়া যাচ্ছে। কাপড় হালকা, গরমে আরামদায়ক।', 'image' => null],
                'cta' => ['label' => 'অর্ডার করুন', 'delivery' => 'সারা দেশে ডেলিভারি • ক্যাশ অন ডেলিভারি'],
                'contact' => ['phone' => ''],
                'features' => [
                    ['title' => 'সুতি কাপড়', 'text' => '১০০% সুতি, রঙ উঠে না'],
                    ['title' => 'সঠিক সাইজ', 'text' => 'M থেকে XXL পর্যন্ত'],
                    ['title' => 'দ্রুত ডেলিভারি', 'text' => 'ঢাকায় ১-২ দিনে']
                ],
                'faq' => [
                    ['q' => 'সাইজ না মিললে?', 'a' => '৭ দিনের মধ্যে বদলে নেওয়া যাবে।'],
                    ['q' => 'ক্যাশ অন ডেলিভারি আছে?', 'a' => 'হ্যাঁ, সারা দেশে ক্যাশ অন ডেলিভারি।']
                ],
                'sections' => ['features' => true, 'faq' => true],
            ];

        $content1 = [
                'brand' => ['name' => 'গ্লো কেয়ার', 'color' => '#123f36', 'accent' => '#e9f5b0', 'logo' => null],
                'hero' => ['badge' => 'ডার্মাটোলজিস্ট-টেস্টেড', 'headline' => 'প্রতিদিনের যত্নে ত্বকের স্বাভাবিক উজ্জ্বলতা', 'subline' => 'হালকা ফর্মুলা, কোনো কড়া গন্ধ নেই—সকাল-রাতে ব্যবহারের জন্য।'],
                'product' => ['name' => 'ভিটামিন-সি সিরাম', 'price' => '১৪৫০', 'sale_price' => '১১৯০', 'description' => '৩০ মিলি বোতল। নিয়মিত ব্যবহারে ত্বকের উজ্জ্বলতা বাড়াতে সাহায্য করে।', 'image' => null],
                'cta' => ['label' => 'অর্ডার করুন', 'delivery' => 'সারা দেশে ডেলিভারি • ক্যাশ অন ডেলিভারি'],
                'contact' => ['phone' => ''],
                'features' => [
                    ['title' => 'হালকা ফর্মুলা', 'text' => 'ত্বকে দ্রুত মিশে যায়'],
                    ['title' => 'প্যারাবেন-মুক্ত', 'text' => 'কোনো কড়া গন্ধ নেই'],
                    ['title' => 'সব ত্বকের জন্য', 'text' => 'সংবেদনশীল ত্বকেও ব্যবহারযোগ্য']
                ],
                'faq' => [
                    ['q' => 'কতদিনে ফল পাব?', 'a' => 'নিয়মিত ব্যবহারে ৩-৪ সপ্তাহে পার্থক্য বোঝা যায়।'],
                    ['q' => 'অর্ডার কীভাবে?', 'a' => 'ফোনে বা মেসেজে অর্ডার করুন।']
                ],
                'sections' => ['features' => true, 'faq' => true],
            ];

        $content2 = [
                'brand' => ['name' => 'স্বাদের ঘর', 'color' => '#5a2f16', 'accent' => '#f2c14e', 'logo' => null],
                'hero' => ['badge' => 'ঘরে বানানো', 'headline' => 'ঘরে বানানো মসলা, সরাসরি আপনার রান্নাঘরে', 'subline' => 'প্রতিটি ব্যাচ হাতে মেশানো—কোনো প্রিজারভেটিভ নেই।'],
                'product' => ['name' => 'হলুদ গুঁড়া (২৫০ গ্রাম)', 'price' => '৩৫০', 'sale_price' => '২৯০', 'description' => 'নিজেদের বাগানের হলুদ, রোদে শুকিয়ে হাতে গুঁড়ো করা।', 'image' => null],
                'cta' => ['label' => 'অর্ডার করুন', 'delivery' => 'সারা দেশে ডেলিভারি • ক্যাশ অন ডেলিভারি'],
                'contact' => ['phone' => ''],
                'features' => [
                    ['title' => 'খাঁটি মসলা', 'text' => 'কোনো মেশানো রঙ নেই'],
                    ['title' => 'হাতে প্রস্তুত', 'text' => 'ছোট ব্যাচে তৈরি'],
                    ['title' => 'সঠিক ওজন', 'text' => 'ওজন যাচাই করা']
                ],
                'faq' => [
                    ['q' => 'কতদিন ভালো থাকে?', 'a' => '৬ মাস, শুকনো পাত্রে রাখলে।'],
                    ['q' => 'বাল্ক অর্ডার?', 'a' => '৫০০ গ্রামের প্যাকেও পাওয়া যায়।']
                ],
                'sections' => ['features' => true, 'faq' => true],
            ];

        $content3 = [
                'brand' => ['name' => 'গ্যাজেট বক্স', 'color' => '#0f172a', 'accent' => '#38bdf8', 'logo' => null],
                'hero' => ['badge' => 'ওয়ারেন্টিসহ', 'headline' => 'হেডফোন, পাওয়ারব্যাংক আর দৈনন্দিন গ্যাজেট', 'subline' => 'অরিজিনাল পণ্য, ৬ মাসের ওয়ারেন্টি, ঢাকায় সেম-ডে ডেলিভারি।'],
                'product' => ['name' => 'ওয়্যারলেস ইয়ারবাড', 'price' => '২৪০০', 'sale_price' => '১৮৯০', 'description' => 'ব্লুটুথ ৫.৩, ২৪ ঘণ্টা ব্যাকআপ, কেসসহ।', 'image' => null],
                'cta' => ['label' => 'অর্ডার করুন', 'delivery' => 'সারা দেশে ডেলিভারি • ক্যাশ অন ডেলিভারি'],
                'contact' => ['phone' => ''],
                'features' => [
                    ['title' => 'ওয়ারেন্টি', 'text' => '৬ মাসের সার্ভিস ওয়ারেন্টি'],
                    ['title' => 'অরিজিনাল', 'text' => 'ইমপোর্টেড অরিজিনাল পণ্য'],
                    ['title' => 'দ্রুত ডেলিভারি', 'text' => 'ঢাকায় সেম-ডে']
                ],
                'faq' => [
                    ['q' => 'ওয়ারেন্টি কীভাবে?', 'a' => 'বক্সের রসিদ দেখালেই সার্ভিস পাবেন।'],
                    ['q' => 'রিটার্ন?', 'a' => '৩ দিনের মধ্যে সমস্যা হলে বদলে দেওয়া হবে।']
                ],
                'sections' => ['features' => true, 'faq' => true],
            ];

        $content4 = [
                'brand' => ['name' => 'হাতের কারুকাজ', 'color' => '#3b2a4a', 'accent' => '#f0a6ca', 'logo' => null],
                'hero' => ['badge' => 'হাতে তৈরি', 'headline' => 'হাতে বোনা গিফট, যত্নে প্যাক করা', 'subline' => 'প্রতিটি পণ্য হাতে তৈরি—ছোট স্টুডিও, বড় যত্ন।'],
                'product' => ['name' => 'হাতে বোনা ঝুড়ি', 'price' => '৮৫০', 'sale_price' => '৭২০', 'description' => 'প্রাকৃতিক সলিড কাঠের ঝুড়ি, হাতে বোনা।', 'image' => null],
                'cta' => ['label' => 'অর্ডার করুন', 'delivery' => 'সারা দেশে ডেলিভারি • ক্যাশ অন ডেলিভারি'],
                'contact' => ['phone' => ''],
                'features' => [
                    ['title' => 'হাতে তৈরি', 'text' => 'প্রতিটি পণ্য আলাদা'],
                    ['title' => 'গিফট প্যাকিং', 'text' => 'উপহারের জন্য প্যাক করা'],
                    ['title' => 'টেকসই', 'text' => 'প্রাকৃতিক উপকরণ']
                ],
                'faq' => [
                    ['q' => 'কাস্টম অর্ডার?', 'a' => 'নাম লিখে দেওয়া যায়।'],
                    ['q' => 'ডেলিভারি?', 'a' => '৩-৫ দিনে সারা দেশে।']
                ],
                'sections' => ['features' => true, 'faq' => true],
            ];
        $schema = [
                ['key' => 'brand.name', 'label' => 'ব্র্যান্ডের নাম', 'type' => 'text', 'required' => true, 'group' => 'ব্র্যান্ড'],
                ['key' => 'brand.logo', 'label' => 'লোগো', 'type' => 'image', 'group' => 'ব্র্যান্ড'],
                ['key' => 'brand.color', 'label' => 'ব্র্যান্ডের রঙ', 'type' => 'color', 'group' => 'ব্র্যান্ড'],
                ['key' => 'brand.accent', 'label' => 'অ্যাকসেন্ট রঙ', 'type' => 'color', 'group' => 'ব্র্যান্ড'],
                ['key' => 'hero.badge', 'label' => 'ছোট ব্যাজ', 'type' => 'text', 'group' => 'হিরো'],
                ['key' => 'hero.headline', 'label' => 'শিরোনাম', 'type' => 'text', 'required' => true, 'group' => 'হিরো'],
                ['key' => 'hero.subline', 'label' => 'সহায়ক লেখা', 'type' => 'textarea', 'group' => 'হিরো'],
                ['key' => 'product.name', 'label' => 'পণ্যের নাম', 'type' => 'text', 'required' => true, 'group' => 'পণ্য'],
                ['key' => 'product.price', 'label' => 'নিয়মিত দাম', 'type' => 'price', 'required' => true, 'group' => 'পণ্য'],
                ['key' => 'product.sale_price', 'label' => 'অফার দাম', 'type' => 'price', 'group' => 'পণ্য'],
                ['key' => 'product.description', 'label' => 'পণ্যের বিবরণ', 'type' => 'textarea', 'group' => 'পণ্য'],
                ['key' => 'product.image', 'label' => 'পণ্যের ছবি', 'type' => 'image', 'group' => 'পণ্য'],
                ['key' => 'cta.label', 'label' => 'বাটনের লেখা', 'type' => 'text', 'group' => 'অর্ডার'],
                ['key' => 'cta.delivery', 'label' => 'ডেলিভারি তথ্য', 'type' => 'text', 'group' => 'অর্ডার'],
                ['key' => 'contact.phone', 'label' => 'যোগাযোগের নম্বর', 'type' => 'text', 'group' => 'যোগাযোগ'],
                ['key' => 'features', 'label' => 'সুবিধা', 'type' => 'list', 'item' => [['key' => 'title', 'label' => 'শিরোনাম'], ['key' => 'text', 'label' => 'বিবরণ']], 'group' => 'সুবিধা'],
                ['key' => 'faq', 'label' => 'সচরাচর প্রশ্ন', 'type' => 'list', 'item' => [['key' => 'q', 'label' => 'প্রশ্ন'], ['key' => 'a', 'label' => 'উত্তর']], 'group' => 'FAQ'],
                ['key' => 'sections.features', 'label' => 'সুবিধা সেকশন দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
                ['key' => 'sections.faq', 'label' => 'FAQ সেকশন দেখান', 'type' => 'toggle', 'group' => 'সেকশন'],
            ];
        $groups = [
            ['slug' => 'fashion', 'label' => 'ফ্যাশন', 'keywords' => 'পোশাক, জামা, কাপড়, fashion, clothing, dress, apparel'],
            ['slug' => 'beauty', 'label' => 'বিউটি ও কেয়ার', 'keywords' => 'স্কিনকেয়ার, ত্বক, মেকআপ, beauty, skincare, cosmetics, care'],
            ['slug' => 'food', 'label' => 'খাবার', 'keywords' => 'হোমমেড খাবার, বেকারি, মধু, মসলা, food, bakery, honey, spice'],
            ['slug' => 'home', 'label' => 'ঘর ও জীবনযাপন', 'keywords' => 'কিচেন, হোম ডেকর, দৈনন্দিন পণ্য, home, kitchen, decor, lifestyle'],
            ['slug' => 'gadget', 'label' => 'প্রযুক্তি', 'keywords' => 'গ্যাজেট, মোবাইল অ্যাকসেসরিজ, tech, gadget, mobile, electronics'],
            ['slug' => 'other', 'label' => 'অন্যান্য', 'keywords' => 'শিশুদের পণ্য, গিফট, হ্যান্ডমেড, kids, gift, handmade'],
        ];

        $children = [
            'poshak' => ['পোশাক', 'fashion', 'কুর্তি, শার্ট, পাঞ্জাবি, panjabi, kurti, shirt'],
            'shari' => ['শাড়ি', 'fashion', 'saree, shari, jamdani'],
            'juta' => ['জুতা', 'fashion', 'shoe, shoes, sandal, juta'],
            'bag' => ['ব্যাগ', 'fashion', 'bag, handbag, backpack'],
            'goyna' => ['গয়না', 'fashion', 'jewellery, jewelry, necklace, goyna'],
            'skincare' => ['স্কিনকেয়ার', 'beauty', 'serum, cream, face wash, skincare'],
            'haircare' => ['হেয়ারকেয়ার', 'beauty', 'shampoo, hair oil, haircare'],
            'cosmetics' => ['কসমেটিকস', 'beauty', 'lipstick, makeup, cosmetics'],
            'homemade' => ['হোমমেড খাবার', 'food', 'homemade, ghee, achar, pickle, food'],
            'bakery' => ['বেকারি', 'food', 'cake, cookies, bakery'],
            'honey' => ['মধু', 'food', 'honey, modhu'],
            'spice' => ['মসলা', 'food', 'spice, masala, turmeric, moshla'],
            'kitchen' => ['কিচেন', 'home', 'kitchen, cookware, pan'],
            'decor' => ['হোম ডেকর', 'home', 'decor, furniture, light'],
            'daily' => ['দৈনন্দিন পণ্য', 'home', 'daily, household, everyday'],
            'gadget' => ['গ্যাজেট', 'gadget', 'gadget, earbuds, headphone, powerbank'],
            'mobile' => ['মোবাইল অ্যাকসেসরিজ', 'gadget', 'mobile, phone, case, charger'],
            'kids' => ['শিশুদের পণ্য', 'other', 'kids, baby, toy, children'],
            'gift' => ['গিফট', 'other', 'gift, present, hamper'],
            'handmade' => ['হ্যান্ডমেড পণ্য', 'other', 'handmade, craft, basket'],
        ];

        foreach ($groups as $index => $group) {
            Category::updateOrCreate(
                ['slug' => $group['slug']],
                [
                    'label' => $group['label'],
                    'keywords' => $group['keywords'],
                    'parent_id' => null,
                    'sort_order' => $index,
                    'is_visible' => true,
                ]
            );
        }

        foreach ($children as $slug => [$label, $parent, $keywords]) {
            $parentId = Category::where('slug', $parent)->value('id');

            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'label' => $label,
                    'keywords' => $keywords,
                    'parent_id' => $parentId,
                    'sort_order' => 0,
                    'is_visible' => true,
                ]
            );
        }

        BuilderTemplate::updateOrCreate(
            ['slug' => 'spotlight-boutique'],
            [
                'category_id' => Category::where('slug', 'poshak')->value('id'),
                'name' => 'স্পটলাইট বুটিক',
                'design' => 'spotlight',
                'tagline' => 'পোশাক বিক্রেতার জন্য ছবি-কেন্দ্রিক ডিজাইন',
                'schema' => $schema,
                'content' => $content0,
                'is_published' => true,
                'sort_order' => 0,
            ]
        );
        BuilderTemplate::updateOrCreate(
            ['slug' => 'story-glow'],
            [
                'category_id' => Category::where('slug', 'skincare')->value('id'),
                'name' => 'স্টোরি গ্লো',
                'design' => 'story',
                'tagline' => 'স্কিনকেয়ারের জন্য গল্প-ধাঁচের ডিজাইন',
                'schema' => $schema,
                'content' => $content1,
                'is_published' => true,
                'sort_order' => 1,
            ]
        );
        BuilderTemplate::updateOrCreate(
            ['slug' => 'swader-ghor'],
            [
                'category_id' => Category::where('slug', 'homemade')->value('id'),
                'name' => 'স্বাদের ঘর',
                'design' => 'spotlight',
                'tagline' => 'হোমমেড খাবারের জন্য উষ্ণ ডিজাইন',
                'schema' => $schema,
                'content' => $content2,
                'is_published' => true,
                'sort_order' => 2,
            ]
        );
        BuilderTemplate::updateOrCreate(
            ['slug' => 'gadget-story'],
            [
                'category_id' => Category::where('slug', 'gadget')->value('id'),
                'name' => 'গ্যাজেট স্টোরি',
                'design' => 'story',
                'tagline' => 'গ্যাজেট বিক্রেতার জন্য আধুনিক ডিজাইন',
                'schema' => $schema,
                'content' => $content3,
                'is_published' => true,
                'sort_order' => 3,
            ]
        );
        BuilderTemplate::updateOrCreate(
            ['slug' => 'hater-karukaj'],
            [
                'category_id' => Category::where('slug', 'handmade')->value('id'),
                'name' => 'হাতের কারুকাজ',
                'design' => 'spotlight',
                'tagline' => 'হ্যান্ডমেড পণ্যের জন্য উজ্জ্বল ডিজাইন',
                'schema' => $schema,
                'content' => $content4,
                'is_published' => true,
                'sort_order' => 4,
            ]
        );

        \App\Support\Landing\ThemeCatalog::seed();
    }
}
