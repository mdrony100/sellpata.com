<?php

namespace Tests\Feature;

use App\Models\BuilderTemplate;
use App\Models\Store;
use App\Models\User;
use App\Services\Platform\CustomDomainService;
use App\Services\Platform\DnsLookup;
use Database\Seeders\BuilderContentSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class FakeDnsLookup implements DnsLookup
{
    /** @var array<string, list<string>> */
    public array $a = [];

    /** @var array<string, list<string>> */
    public array $cname = [];

    public function aRecords(string $host): array
    {
        return $this->a[$host] ?? [];
    }

    public function cnameTargets(string $host): array
    {
        return $this->cname[$host] ?? [];
    }
}

class CustomDomainTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FakeDnsLookup $dns;

    private BuilderTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);
        $this->template = BuilderTemplate::firstOrFail();
        $this->user = User::create([
            'name' => 'রুমানা',
            'phone' => '+8801712345678',
            'password' => bcrypt('x'),
            'phone_verified_at' => now(),
        ]);
        $this->dns = new FakeDnsLookup;
        $this->app->instance(DnsLookup::class, $this->dns);
        config(['platform.server_ip' => '169.58.90.98']);
    }

    public function test_domain_api_requires_a_signed_in_store(): void
    {
        $this->getJson('/api/domain')->assertUnauthorized();
        $this->actingAs($this->user)->getJson('/api/domain')->assertNotFound();
    }

    public function test_domain_is_normalised_and_stays_pending_until_a_check(): void
    {
        $store = $this->makeStore();
        $this->dns->a['mybrand.com'] = ['169.58.90.98'];

        $this->actingAs($this->user)
            ->postJson('/api/domain', ['domain' => 'HTTPS://www.MyBrand.com/shop/item?q=1#top'])
            ->assertCreated()
            ->assertJsonPath('domain.host', 'mybrand.com')
            ->assertJsonPath('domain.status', 'pending')
            ->assertJsonPath('free_host', 'organicfood.sellpata.com')
            ->assertJsonPath('server_ip', '169.58.90.98')
            ->assertJsonPath('records.0', ['type' => 'A', 'host' => '@', 'value' => '169.58.90.98'])
            ->assertJsonPath('records.1', ['type' => 'CNAME', 'host' => 'www', 'value' => 'organicfood.sellpata.com']);

        $this->assertSame('mybrand.com', $store->fresh()->custom_domain);
        $this->assertNull($store->fresh()->custom_domain_verified_at);

        $this->actingAs($this->user)
            ->postJson('/api/domain', ['domain' => 'mybrand.com'])
            ->assertOk()
            ->assertJsonPath('domain.status', 'pending');
    }

    public function test_platform_and_invalid_domains_are_rejected(): void
    {
        $this->makeStore();

        foreach ([
            '   ',
            'mybrand',
            'localhost',
            '169.58.90.98',
            '-bad.com',
            'sellpata.com',
            'HTTPS://WWW.SellPata.com/pricing',
            'shop.sellpata.com',
            'http://organicfood.sellpata.com/mango',
        ] as $domain) {
            $response = $this->actingAs($this->user)->postJson('/api/domain', ['domain' => $domain]);
            $this->assertSame(422, $response->status(), $domain);
            $this->assertNull($this->user->store->fresh()->custom_domain, $domain);
        }

        $message = $this->actingAs($this->user)->postJson('/api/domain', ['domain' => 'sellpata.com'])->json('message');
        $this->assertIsString($message);
        $this->assertStringContainsString('sellpata.com', $message);
    }

    public function test_a_domain_is_unique_across_stores_until_it_is_removed(): void
    {
        $this->makeStore();
        $this->actingAs($this->user)->postJson('/api/domain', ['domain' => 'mybrand.com'])->assertCreated();

        $other = User::create(['name' => 'রহিম', 'phone' => '+8801812345678', 'password' => bcrypt('x')]);
        $this->actingAs($other)->postJson('/api/store', ['username' => 'rahimstore', 'display_name' => 'রহিম'])->assertSuccessful();
        $other->unsetRelation('store');
        $this->actingAs($other)
            ->postJson('/api/domain', ['domain' => 'https://www.MyBrand.com/hello'])
            ->assertStatus(422);

        $this->assertNull(Store::where('username_lower', 'rahimstore')->firstOrFail()->custom_domain);

        $this->actingAs($this->user)
            ->postJson('/api/domain', ['domain' => 'other.com'])
            ->assertStatus(422);
        $this->assertSame('mybrand.com', $this->user->store->fresh()->custom_domain);

        $this->actingAs($this->user)->deleteJson('/api/domain')->assertOk()->assertJsonPath('domain', null);
        $this->assertNull($this->user->store->fresh()->custom_domain);

        $this->actingAs($other)->postJson('/api/domain', ['domain' => 'mybrand.com'])->assertCreated();
    }

    public function test_apex_a_record_matching_the_configured_ip_verifies(): void
    {
        $store = $this->makeStore();
        $this->actingAs($this->user)->postJson('/api/domain', ['domain' => 'mybrand.com'])->assertCreated();

        config(['platform.server_ip' => '203.0.113.50']);
        $this->dns->a['mybrand.com'] = ['169.58.90.98', '198.51.100.8'];

        $this->actingAs($this->user)
            ->postJson('/api/domain/check')
            ->assertOk()
            ->assertJsonPath('domain.status', 'failed')
            ->assertJsonPath('server_ip', '203.0.113.50')
            ->assertJsonPath('domain.verified_at', null);

        $this->dns->a['mybrand.com'] = ['198.51.100.8', '203.0.113.50'];

        $this->actingAs($this->user)
            ->postJson('/api/domain/check')
            ->assertOk()
            ->assertJsonPath('domain.status', 'verified');

        $this->assertNotNull($store->fresh()->custom_domain_verified_at);
        $this->assertNotNull($store->fresh()->custom_domain_checked_at);
    }

    public function test_www_cname_to_the_store_host_verifies_without_an_a_record(): void
    {
        $this->makeStore();
        $this->actingAs($this->user)->postJson('/api/domain', ['domain' => 'mybrand.com'])->assertCreated();

        $this->dns->cname['www.mybrand.com'] = ['other.sellpata.com'];
        $this->actingAs($this->user)->postJson('/api/domain/check')->assertOk()->assertJsonPath('domain.status', 'failed');

        $this->dns->cname['www.mybrand.com'] = ['OrganicFood.SellPata.com.'];
        $this->actingAs($this->user)->postJson('/api/domain/check')->assertOk()->assertJsonPath('domain.status', 'verified');
    }

    public function test_check_without_a_domain_is_rejected(): void
    {
        $this->makeStore();
        $this->actingAs($this->user)->postJson('/api/domain/check')->assertStatus(422);
    }

    public function test_verified_custom_domain_serves_the_store_and_other_hosts_do_not(): void
    {
        $store = $this->makeStore();
        $this->publish($store);
        $this->actingAs($this->user)->postJson('/api/domain', ['domain' => 'mybrand.com'])->assertCreated();

        $pending = $this->get('http://mybrand.com/');
        $pending->assertOk();
        $this->assertStringNotContainsString('__STORE__', $pending->getContent());

        $this->dns->a['mybrand.com'] = ['169.58.90.98'];
        $this->actingAs($this->user)->postJson('/api/domain/check')->assertOk()->assertJsonPath('domain.status', 'verified');

        $this->get('http://mybrand.com/')
            ->assertOk()
            ->assertSee('__STORE__', false)
            ->assertSee('সেরা আম', false);

        $this->get('http://www.mybrand.com/mango')
            ->assertOk()
            ->assertSee('সেরা আম', false);

        $this->get('http://WWW.MyBrand.com/')
            ->assertOk()
            ->assertSee('__STORE__', false);

        $this->postJson('http://mybrand.com/checkout', [
            'customer_name' => 'করিম',
            'customer_phone' => '01712345678',
            'address' => 'ঢাকা',
        ])->assertCreated();

        $this->postJson('http://www.mybrand.com/checkout', [
            'customer_name' => 'করিম',
            'customer_phone' => '01712345679',
            'address' => 'ঢাকা',
        ])->assertCreated();

        $this->get('http://organicfood.sellpata.com/')->assertOk()->assertSee('__STORE__', false);
        $this->get('http://someoneelse.sellpata.com/')->assertNotFound();

        $unknown = $this->get('http://otherbrand.com/');
        $unknown->assertOk();
        $this->assertStringNotContainsString('__STORE__', $unknown->getContent());

        $this->get('http://sellpata.com/dashboard')->assertOk();
        $this->assertStringNotContainsString('__STORE__', $this->get('http://sellpata.com/dashboard')->getContent());
        $this->get('http://mybrand.com/dashboard')->assertNotFound()->assertSee('প্রকাশ করা হয়নি', false);

        $store->forceFill([
            'custom_domain_status' => CustomDomainService::FAILED,
            'custom_domain_verified_at' => null,
        ])->save();

        $failed = $this->get('http://mybrand.com/');
        $failed->assertOk();
        $this->assertStringNotContainsString('__STORE__', $failed->getContent());
    }

    public function test_verified_command_and_localhost_ask_endpoint(): void
    {
        $first = $this->makeStore();
        $first->forceFill([
            'custom_domain' => 'mybrand.com',
            'custom_domain_status' => CustomDomainService::VERIFIED,
            'custom_domain_verified_at' => now(),
        ])->save();

        $other = User::create(['name' => 'রহিম', 'phone' => '+8801812345678', 'password' => bcrypt('x')]);
        $this->actingAs($other)->postJson('/api/store', ['username' => 'rahimstore', 'display_name' => 'রহিম'])->assertSuccessful();
        Store::where('username_lower', 'rahimstore')->firstOrFail()->forceFill([
            'custom_domain' => 'alpha.com',
            'custom_domain_status' => CustomDomainService::VERIFIED,
            'custom_domain_verified_at' => now(),
        ])->save();

        $third = User::create(['name' => 'করিম', 'phone' => '+8801912345678', 'password' => bcrypt('x')]);
        $this->actingAs($third)->postJson('/api/store', ['username' => 'karimstore', 'display_name' => 'করিম'])->assertSuccessful();
        Store::where('username_lower', 'karimstore')->firstOrFail()->forceFill([
            'custom_domain' => 'waiting.com',
            'custom_domain_status' => CustomDomainService::PENDING,
        ])->save();

        Artisan::call('domains:verified');
        $this->assertSame("alpha.com\nmybrand.com\n", Artisan::output());

        $this->get('http://127.0.0.1/internal/domain-check?domain=mybrand.com')->assertOk()->assertSee('ok');
        $this->get('http://127.0.0.1/internal/domain-check?domain=WWW.MyBrand.com')->assertOk();
        $this->get('http://localhost/internal/domain-check?domain=https://alpha.com/path')->assertOk();
        $this->get('http://127.0.0.1/internal/domain-check?domain=waiting.com')->assertNotFound();
        $this->get('http://127.0.0.1/internal/domain-check?domain=unknown.com')->assertNotFound();
        $this->get('http://127.0.0.1/internal/domain-check?domain=sellpata.com')->assertNotFound();
        $this->get('http://127.0.0.1/internal/domain-check')->assertNotFound();
        $this->get('http://sellpata.com/internal/domain-check?domain=mybrand.com')->assertForbidden();
        $this->get('http://mybrand.com/internal/domain-check?domain=mybrand.com')->assertForbidden();
    }

    public function test_ask_endpoint_refuses_a_public_client_address(): void
    {
        $store = $this->makeStore();
        $store->forceFill([
            'custom_domain' => 'mybrand.com',
            'custom_domain_status' => CustomDomainService::VERIFIED,
        ])->save();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.8'])
            ->get('http://127.0.0.1/internal/domain-check?domain=mybrand.com')
            ->assertForbidden();
    }

    private function makeStore(string $username = 'organicfood'): Store
    {
        $this->actingAs($this->user)->postJson('/api/store', [
            'username' => $username,
            'display_name' => 'অর্গানিক ফুড',
        ])->assertSuccessful();

        // The setup request cached store=null on this user instance.
        $this->user->unsetRelation('store');

        return Store::where('username_lower', $username)->firstOrFail();
    }

    private function publish(Store $store): void
    {
        $page = $store->pages()->create([
            'template_id' => $this->template->id,
            'title' => 'আমের হাট',
            'slug' => 'mango',
            'status' => 'draft',
            'content' => [
                'brand' => ['name' => 'অর্গানিক ফুড'],
                'hero' => ['headline' => 'সেরা আম', 'subline' => 'সরাসরি বাগান থেকে'],
                'product' => ['name' => 'হিমসাগর আম', 'price' => '990', 'sale_price' => null],
            ],
            'revision' => 1,
        ]);

        $this->actingAs($store->user)->postJson("/api/pages/{$page->id}/publish")->assertOk();
    }
}
