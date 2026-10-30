<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $base = ['cod' => true, 'subdomain' => true, 'invoice_pdf' => true, 'online_payment' => false,
            'courier' => false, 'meta_pixel' => false, 'ga4' => false, 'custom_domain' => false,
            'remove_branding' => false, 'csv_export' => false, 'advanced_analytics' => false];

        $plans = [
            ['slug' => 'free', 'name' => 'ফ্রি', 'max_pages' => 1, 'sort_order' => 1, 'features' => $base],
            ['slug' => 'starter', 'name' => 'স্টার্টার', 'max_pages' => 3, 'sort_order' => 2,
                'features' => array_merge($base, ['online_payment' => true, 'meta_pixel' => true, 'remove_branding' => true, 'csv_export' => true])],
            ['slug' => 'growth', 'name' => 'গ্রোথ', 'max_pages' => 10, 'sort_order' => 3,
                'features' => array_merge($base, ['online_payment' => true, 'meta_pixel' => true, 'remove_branding' => true, 'csv_export' => true, 'courier' => true, 'ga4' => true, 'custom_domain' => true, 'advanced_analytics' => true])],
            ['slug' => 'business', 'name' => 'বিজনেস', 'max_pages' => 30, 'sort_order' => 4,
                'features' => array_merge($base, ['online_payment' => true, 'meta_pixel' => true, 'remove_branding' => true, 'csv_export' => true, 'courier' => true, 'ga4' => true, 'custom_domain' => true, 'advanced_analytics' => true])],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan + ['currency' => 'BDT', 'is_active' => true]);
        }
    }
}
