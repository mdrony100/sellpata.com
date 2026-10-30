<?php

namespace Tests\Feature;

use App\Models\BuilderTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Store;
use App\Models\User;
use App\Services\Platform\CheckoutOptions;
use App\Support\Landing\ThemeCatalog;
use Database\Seeders\BuilderContentSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingThemesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);
    }

    public function test_five_themes_are_registered_with_distinct_defaults(): void
    {
        $themes = BuilderTemplate::whereIn('design', ThemeCatalog::DESIGNS)->orderBy('sort_order')->get();

        $this->assertCount(5, $themes);
        $this->assertSame(
            ['prakritik', 'abhijatya', 'smart', 'komol', 'sorasari'],
            $themes->pluck('design')->all()
        );

        $modes = $themes->map(fn ($theme) => $theme->content['hero']['mode'])->all();
        $this->assertSame(['split', 'banner', 'video', 'split', 'banner'], $modes);
        $this->assertSame([], $themes->firstWhere('design', 'sorasari')->content['reviews']);
        $this->assertTrue($themes->firstWhere('design', 'prakritik')->content['reviews'][0]['sample']);
    }

    public function test_demo_routes_are_noindex_and_do_not_offer_checkout(): void
    {
        foreach (ThemeCatalog::DESIGNS as $slug) {
            $this->get("/demo/{$slug}")
                ->assertOk()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

            $payload = $this->getJson("/api/demo/{$slug}")->assertOk();
            $payload->assertJsonPath('design', $slug);
            $this->assertArrayNotHasKey('order_endpoint', $payload->json());
            $this->assertNotEmpty($payload->json('content.hero.headline'));
        }
    }

    public function test_variant_price_and_area_charge_are_computed_on_the_server(): void
    {
        [$user, $store] = $this->store('modhu');
        $template = BuilderTemplate::where('slug', 'prakritik')->firstOrFail();
        $page = $store->pages()->create([
            'template_id' => $template->id,
            'title' => 'মধু',
            'slug' => 'honey',
            'status' => 'published',
            'is_primary' => true,
            'content' => $template->content,
            'published_content' => $template->content,
            'published_at' => now(),
            'revision' => 1,
        ]);

        $live = $this->get('http://modhu.sellpata.com/');
        $live->assertOk();
        $this->assertStringNotContainsString('নমুনা ক্রেতা', $live->getContent());
        $this->assertStringNotContainsString('connect.facebook.net', $live->getContent());

        $response = $this->postJson('http://modhu.sellpata.com/checkout', [
            'customer_name' => 'করিম',
            'customer_phone' => '01712345678',
            'address' => 'ধানমন্ডি, ঢাকা',
            'area_id' => 'dhaka',
            'variant_id' => '500',
            'quantity' => 2,
            'payment_method' => 'cod',
            'total' => 1,
        ]);

        $response->assertCreated();
        $order = Order::firstOrFail();
        $this->assertSame(1380, $order->subtotal);
        $this->assertSame(60, $order->delivery_charge);
        $this->assertSame(1440, $order->total);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('new', $order->status);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('৫০০ গ্রাম', $order->items()->first()->variant);
        $this->assertSame($page->id, $order->page_id);
    }

    public function test_unavailable_variant_unknown_area_and_fake_gateway_are_rejected(): void
    {
        [$user, $store] = $this->store('nokshi');
        $template = BuilderTemplate::where('slug', 'abhijatya')->firstOrFail();
        $store->pages()->create([
            'template_id' => $template->id,
            'title' => 'শাড়ি',
            'slug' => 'saree',
            'status' => 'published',
            'is_primary' => true,
            'content' => $template->content,
            'published_content' => $template->content,
            'published_at' => now(),
            'revision' => 1,
        ]);

        $this->postJson('http://nokshi.sellpata.com/checkout', [
            'customer_name' => 'করিম',
            'customer_phone' => '01712345678',
            'address' => 'ঢাকা',
            'area_id' => 'dhaka',
            'variant_id' => 'nil-13',
            'quantity' => 1,
        ])->assertStatus(422);

        $this->postJson('http://nokshi.sellpata.com/checkout', [
            'customer_name' => 'করিম',
            'customer_phone' => '01712345678',
            'address' => 'ঢাকা',
            'area_id' => 'nowhere',
            'variant_id' => 'lal-12',
            'quantity' => 1,
        ])->assertStatus(422);

        $this->postJson('http://nokshi.sellpata.com/checkout', [
            'customer_name' => 'করিম',
            'customer_phone' => '01712345678',
            'address' => 'ঢাকা',
            'area_id' => 'dhaka',
            'variant_id' => 'lal-12',
            'payment_method' => 'online',
        ])->assertStatus(422);
    }

    public function test_paid_plan_still_hides_online_payment_until_a_gateway_exists(): void
    {
        [$user, $store] = $this->store('paidshop');
        $growth = Plan::where('slug', 'growth')->firstOrFail();
        $store->subscriptions()->update(['plan_id' => $growth->id]);

        $this->assertTrue($store->fresh()->plan()->has('online_payment'));
        $this->assertFalse(CheckoutOptions::gatewayReady());
        $ids = array_column(CheckoutOptions::paymentMethods($store->fresh()), 'id');
        $this->assertSame(['cod'], $ids);
        $this->assertFalse(CheckoutOptions::courierAvailable($store->fresh()));
    }

    public function test_idempotency_key_does_not_create_a_second_order(): void
    {
        [$user, $store] = $this->store('bottle');
        $template = BuilderTemplate::where('slug', 'sorasari')->firstOrFail();
        $store->pages()->create([
            'template_id' => $template->id,
            'title' => 'বোতল',
            'slug' => 'bottle',
            'status' => 'published',
            'is_primary' => true,
            'content' => $template->content,
            'published_content' => $template->content,
            'published_at' => now(),
            'revision' => 1,
        ]);

        $body = [
            'customer_name' => 'করিম',
            'customer_phone' => '01712345678',
            'address' => 'ঢাকা',
            'area_id' => 'outside',
            'bundle_id' => 'pair',
            'quantity' => 1,
        ];

        $first = $this->withHeader('Idempotency-Key', 'same-tap')->postJson('http://bottle.sellpata.com/checkout', $body);
        $first->assertCreated();
        $second = $this->withHeader('Idempotency-Key', 'same-tap')->postJson('http://bottle.sellpata.com/checkout', $body);
        $second->assertOk();
        $this->assertSame($first->json('reference'), $second->json('reference'));
        $this->assertSame(1, Order::count());
        $this->assertSame(1650, Order::first()->subtotal);
        $this->assertSame(120, Order::first()->delivery_charge);
    }

    public function test_switching_theme_keeps_copy_and_takes_the_new_hero_mode(): void
    {
        $from = BuilderTemplate::where('slug', 'prakritik')->firstOrFail();
        $to = BuilderTemplate::where('slug', 'smart')->firstOrFail();
        $merged = ThemeCatalog::mergeContent($from->content, $to->content);

        $this->assertSame('সরিষা ফুলের মধু, কাঁচের বয়ামে', $merged['hero']['headline']);
        $this->assertSame('video', $merged['hero']['mode']);
        $this->assertSame('বনের মধু', $merged['brand']['name']);
    }

    public function test_admin_can_hide_a_theme_without_changing_a_published_page(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'themes@example.test',
            'password' => bcrypt('secret-pass'),
            'is_admin' => true,
        ]);

        [$user, $store] = $this->store('keepshop');
        $template = BuilderTemplate::where('slug', 'komol')->firstOrFail();
        $page = $store->pages()->create([
            'template_id' => $template->id,
            'title' => 'তেল',
            'slug' => 'oil',
            'status' => 'published',
            'is_primary' => true,
            'content' => $template->content,
            'published_content' => $template->content,
            'published_at' => now(),
            'revision' => 1,
        ]);

        $this->actingAs($admin)->put(route('admin.landing-themes.update', $template), [
            'name' => 'কোমল',
            'tagline' => $template->tagline,
            'category_id' => $template->category_id,
            'theme_version' => '1.0.1',
            'sort_order' => $template->sort_order,
            'is_published' => '0',
        ])->assertRedirect(route('admin.landing-themes.index'));

        $this->assertFalse($template->fresh()->is_published);
        $this->assertSame('নারকেল তেল ও গোলাপজল', $page->fresh()->published_content['hero']['headline']);

        $this->getJson('/api/discovery?category=haircare')
            ->assertOk()
            ->assertJsonMissing(['slug' => 'komol']);
    }

    /** @return array{0: User, 1: Store} */
    private function store(string $username): array
    {
        $user = User::create([
            'name' => 'বিক্রেতা',
            'phone' => '+88017'.random_int(10000000, 99999999),
            'password' => bcrypt('x'),
            'phone_verified_at' => now(),
        ]);

        $this->actingAs($user)->postJson('/api/store', [
            'username' => $username,
            'display_name' => 'দোকান',
        ])->assertSuccessful();

        return [$user, Store::where('username_lower', $username)->firstOrFail()];
    }
}
