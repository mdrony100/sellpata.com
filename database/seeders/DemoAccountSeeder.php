<?php

namespace Database\Seeders;

use App\Models\Draft;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Models\BuilderTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Accounts and sample data used for the demo recordings and manual testing. */
class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@sellpata.com'],
            ['name' => 'SellPata Admin', 'phone' => '+8801700000000', 'password' => Hash::make('sellpata-admin'), 'is_admin' => true],
        );

        $customer = User::updateOrCreate(
            ['phone' => '+8801711000001'],
            ['name' => 'ডেমো উদ্যোক্তা', 'email' => 'demo@sellpata.com', 'password' => Hash::make('demo-customer-2026')],
        );

        $store = Store::firstOrCreate(
            ['user_id' => $customer->id],
            ['username' => 'demo-shop', 'username_lower' => 'demo-shop', 'display_name' => 'ডেমো শপ', 'contact_phone' => '01711000001', 'delivery_charge' => 80],
        );

        $free = Plan::where('slug', 'free')->first();
        if ($free && ! $store->subscriptions()->where('status', 'active')->exists()) {
            Subscription::create([
                'store_id' => $store->id, 'plan_id' => $free->id, 'status' => 'active',
                'source' => 'free', 'started_at' => now(),
            ]);
        }

        $source = Page::whereNotNull('published_content')->first() ?? Page::first();

        try {
        if ($source && ! $store->pages()->exists()) {
            $page = $store->pages()->create([
                'template_id' => $source->template_id,
                'title' => 'আমের মৌসুম',
                'slug' => 'mango',
                'status' => 'published',
                'is_primary' => true,
                'content' => $source->content,
                'published_content' => $source->published_content ?? $source->content,
                'published_at' => now(),
                'revision' => 1,
            ]);

            \Illuminate\Support\Facades\DB::table('pages')->where('id', $page->id)->update(['is_primary' => 1]);
        }
        } catch (\Throwable $e) {
            $this->command?->warn('page skipped: '.$e->getMessage());
        }

        try {
        if (! $store->orders()->exists()) {
            foreach ([['করিম আহমেদ', '01712345678', 'মিরপুর, ঢাকা', 'new'], ['সাদিয়া ইসলাম', '01812345678', 'উত্তরা, ঢাকা', 'delivered']] as $i => [$name, $phone, $address, $status]) {
                $order = Order::create([
                    'store_id' => $store->id,
                    'reference' => 'DEMO-'.(1001 + $i),
                    'customer_name' => $name,
                    'customer_phone' => $phone,
                    'address' => $address,
                    'subtotal' => 1200,
                    'delivery_charge' => 80,
                    'total' => 1280,
                    'payment_method' => 'cod',
                    'payment_status' => $status === 'delivered' ? 'paid' : 'unpaid',
                    'status' => $status,
                    'snapshot' => [],
                ]);

                OrderItem::create([
                    'order_id' => $order->id, 'name' => 'মিষ্টি আম (৫ কেজি)',
                    'price' => 1200, 'quantity' => 1, 'line_total' => 1200,
                ]);
            }
        }
        } catch (\Throwable $e) {
            $this->command?->warn('orders skipped: '.$e->getMessage());
        }

        $this->command?->info('admin: admin@sellpata.com / sellpata-admin');
        $this->command?->info('customer: 01711000001 (OTP login, dev code shown on screen)');
    }
}
