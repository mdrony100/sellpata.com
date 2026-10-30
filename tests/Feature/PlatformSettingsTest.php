<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use App\Services\Platform\InvoiceService;
use App\Services\Platform\PlatformSettings;
use Database\Seeders\BuilderContentSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'অ্যাডমিন', 'phone' => '+8801911111111',
            'password' => bcrypt('x'), 'is_admin' => true,
        ]);
    }

    private function payload(array $platform = []): array
    {
        return array_merge([
            'brand' => ['name' => 'SellPata', 'domain' => 'sellpata.com', 'tagline' => 'ল্যান্ডিং পেজ'],
            'youtube_url' => '',
            'template_count_label' => '',
            'features' => ['coming_soon_label' => 'শিগগিরই আসছে'],
            'contact' => ['phone' => '', 'email' => '', 'whatsapp' => ''],
            'platform' => [
                'base_domain' => 'sellpata.com',
                'impersonation_ttl_minutes' => 30,
                'invoice_issuer_name' => 'SellPata',
                'invoice_issuer_address' => '',
                'invoice_issuer_phone' => '',
                'invoice_issuer_email' => '',
                'invoice_vat_note' => '',
            ],
        ], $platform);
    }

    public function test_admin_saves_platform_settings(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', $this->payload([
                'platform' => [
                    'base_domain' => 'sellpata.test',
                    'impersonation_ttl_minutes' => 45,
                    'invoice_issuer_name' => 'সেলপাতা লিমিটেড',
                    'invoice_issuer_address' => 'ধানমন্ডি, ঢাকা',
                    'invoice_issuer_phone' => '01700000000',
                    'invoice_issuer_email' => 'billing@sellpata.test',
                    'invoice_vat_note' => 'ভ্যাট প্রযোজ্য নয়',
                ],
            ]))
            ->assertRedirect();

        $this->assertSame('sellpata.test', Setting::get('platform.base_domain'));
        $this->assertSame('45', Setting::get('platform.impersonation_ttl_minutes'));
        $this->assertSame('সেলপাতা লিমিটেড', Setting::get('platform.invoice.issuer.name'));
        $this->assertSame(45, PlatformSettings::int('platform.impersonation_ttl_minutes', 30));
    }

    public function test_settings_screen_shows_the_platform_section(): void
    {
        $this->actingAs($this->admin())->get('/admin/settings')
            ->assertOk()
            ->assertSee('প্ল্যাটফর্ম')
            ->assertSee('ইনভয়েসে যা ছাপা হবে');
    }

    public function test_base_domain_setting_drives_customer_urls(): void
    {
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);

        $user = User::create(['name' => 'রুমানা', 'phone' => '+8801712345678', 'password' => bcrypt('x')]);
        $this->actingAs($user)->postJson('/api/store', ['username' => 'organicfood'])->assertSuccessful();

        Setting::put('platform.base_domain', 'shop.example');

        $this->assertStringContainsString('organicfood.shop.example', Store::firstOrFail()->publicUrl());
    }

    public function test_invoice_uses_the_configured_issuer(): void
    {
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);

        Setting::put('platform.invoice.issuer.name', 'সেলপাতা লিমিটেড');
        Setting::put('platform.invoice.vat_note', 'ভ্যাট প্রযোজ্য নয়');

        $user = User::create(['name' => 'রুমানা', 'phone' => '+8801712345678', 'password' => bcrypt('x')]);
        $this->actingAs($user)->postJson('/api/store', ['username' => 'organicfood'])->assertSuccessful();
        $store = Store::firstOrFail();

        $order = $store->orders()->create([
            'reference' => 'SP-1', 'customer_name' => 'করিম', 'customer_phone' => '01712345678',
            'address' => 'ঢাকা', 'subtotal' => 500, 'delivery_charge' => 0, 'total' => 500,
            'payment_method' => 'cod', 'payment_status' => 'unpaid', 'status' => 'new', 'snapshot' => [],
        ]);

        \App\Models\Subscription::create([
            'store_id' => $store->id, 'plan_id' => \App\Models\Plan::where('slug', 'starter')->firstOrFail()->id,
            'status' => 'active', 'source' => 'manual', 'started_at' => now(),
        ]);

        // a platform (subscription) invoice is issued by SellPata itself
        $invoice = app(InvoiceService::class)->createSubscriptionInvoice($store->activeSubscription(), 990, 'unpaid');

        $this->assertSame('সেলপাতা লিমিটেড', $invoice->seller_snapshot['name']);


        // the note reaches the rendered invoice document itself
        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'order' => null,
            'plan' => null,
            'vatNote' => app(\App\Services\Platform\PlatformSettings::class)::get('platform.invoice.vat_note', ''),
            'fontUrl' => 'file:///tmp/x.ttf',
        ])->render();

        $this->assertStringContainsString('ভ্যাট প্রযোজ্য নয়', $html);
    }

    public function test_impersonation_uses_the_configured_session_length(): void
    {
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);

        $customer = User::create(['name' => 'রুমানা', 'phone' => '+8801712345678', 'password' => bcrypt('x')]);
        $this->actingAs($customer)->postJson('/api/store', ['username' => 'organicfood'])->assertSuccessful();

        Setting::put('platform.impersonation_ttl_minutes', '15');

        $response = $this->actingAs($this->admin())
            ->postJson('/admin/impersonate/'.$customer->id, ['reason' => 'সাপোর্ট']);

        $response->assertOk();

        $expires = \Carbon\Carbon::parse(session('impersonation_expires'));
        $this->assertEqualsWithDelta(15, now()->diffInMinutes($expires), 1);
    }
}
