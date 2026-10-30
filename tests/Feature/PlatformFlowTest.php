<?php

namespace Tests\Feature;

use App\Models\BuilderTemplate;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\BuilderContentSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private BuilderTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);
        $this->template = BuilderTemplate::firstOrFail();
        $this->user = User::create(['name' => 'রুমানা', 'phone' => '+8801712345678', 'password' => bcrypt('x'), 'phone_verified_at' => now()]);
    }

    private function makeStore(?User $user = null, string $username = 'organicfood'): Store
    {
        $user ??= $this->user;
        $this->actingAs($user)->postJson('/api/store', ['username' => $username, 'display_name' => 'অর্গানিক ফুড'])->assertSuccessful();

        return Store::where('username_lower', $username)->firstOrFail();
    }

    private function makePage(Store $store, string $slug = 'mango', int $price = 990): \App\Models\Page
    {
        return $store->pages()->create([
            'template_id' => $this->template->id,
            'title' => 'আমের হাট',
            'slug' => $slug,
            'status' => 'draft',
            'content' => [
                'brand' => ['name' => 'অর্গানিক ফুড'],
                'hero' => ['headline' => 'সেরা আম', 'subline' => 'সরাসরি বাগান থেকে'],
                'product' => ['name' => 'হিমসাগর আম', 'price' => (string) $price, 'sale_price' => null],
            ],
            'revision' => 1,
        ]);
    }

    public function test_store_creation_assigns_the_free_plan(): void
    {
        $store = $this->makeStore();

        $this->assertSame('free', $store->plan()->slug);
        $this->assertDatabaseHas('subscriptions', ['store_id' => $store->id, 'status' => 'active', 'source' => 'free']);
    }

    public function test_reserved_and_duplicate_usernames_are_rejected(): void
    {
        $this->actingAs(User::find($this->user->id))->postJson('/api/store', ['username' => 'admin'])->assertStatus(422);
        $this->makeStore();

        $other = User::create(['name' => 'রহিম', 'phone' => '+8801812345678', 'password' => bcrypt('x')]);
        $this->actingAs(User::find($other->id))->postJson('/api/store', ['username' => 'OrganicFood'])->assertStatus(422);
    }

    public function test_first_published_page_becomes_the_store_root(): void
    {
        $store = $this->makeStore();
        $page = $this->makePage($store);

        $this->actingAs(User::find($this->user->id))->postJson("/api/pages/{$page->id}/publish")->assertOk();
        $this->assertTrue($page->fresh()->is_primary);

        $response = $this->get('http://organicfood.sellpata.com/');
        $response->assertOk();

        // another store's address must never render this store
        $this->get('http://someoneelse.sellpata.com/')->assertNotFound();
    }

    public function test_extra_pages_are_reachable_at_their_slug_and_drafts_stay_private(): void
    {
        $store = $this->makeStore();
        $page = $this->makePage($store);
        $this->actingAs(User::find($this->user->id))->postJson("/api/pages/{$page->id}/publish");

        $this->get('http://organicfood.sellpata.com/mango')->assertOk();

        // unpublished edits must not leak to visitors until republished
        $page->forceFill(['content' => array_merge($page->content, ['hero' => ['headline' => 'নতুন শিরোনাম']])])->save();
        $live = $this->get('http://organicfood.sellpata.com/mango');
        $this->assertStringContainsString('সেরা আম', $live->getContent());
        $this->assertStringNotContainsString('নতুন শিরোনাম', $live->getContent());
    }

    public function test_free_plan_cannot_create_a_second_page(): void
    {
        $store = $this->makeStore();
        $this->makePage($store, 'mango');

        $this->actingAs(User::find($this->user->id))->postJson('/api/pages', ['draft_id' => 999])->assertNotFound();

        // a duplicate of the only page is refused by the plan limit, not silently allowed
        $page = $store->pages()->first();
        $this->actingAs(User::find($this->user->id))->postJson("/api/pages/{$page->id}/duplicate")->assertStatus(422);
    }

    public function test_cod_checkout_totals_come_from_the_server(): void
    {
        $store = $this->makeStore();
        $store->forceFill(['delivery_charge' => 60, 'delivery_charge_enabled' => true])->save();
        $page = $this->makePage($store, 'mango', 990);
        $this->actingAs(User::find($this->user->id))->postJson("/api/pages/{$page->id}/publish");

        $response = $this->postJson('http://organicfood.sellpata.com/checkout', [
            'customer_name' => 'করিম',
            'customer_phone' => '01712345678',
            'address' => 'ঢাকা',
            'quantity' => 2,
            'total' => 1,          // browser-supplied total must be ignored
        ]);

        $response->assertCreated();
        $order = Order::firstOrFail();
        $this->assertSame(1980, $order->subtotal);
        $this->assertSame(60, $order->delivery_charge);
        $this->assertSame(2040, $order->total);
        $this->assertSame('unpaid', $order->payment_status);   // COD is never paid on creation
        $this->assertSame('new', $order->status);
        $this->assertSame(1, Invoice::where('order_id', $order->id)->count());
    }

    public function test_orders_are_isolated_between_stores(): void
    {
        $store = $this->makeStore();
        $page = $this->makePage($store);
        $this->actingAs(User::find($this->user->id))->postJson("/api/pages/{$page->id}/publish");

        $this->postJson('http://organicfood.sellpata.com/checkout', [
            'customer_name' => 'করিম', 'customer_phone' => '01712345678', 'address' => 'ঢাকা',
        ])->assertCreated();

        $other = User::create(['name' => 'রহিম', 'phone' => '+8801812345678', 'password' => bcrypt('x')]);
        $this->makeStore($other, 'rahimstore');

        $this->actingAs(User::find($other->id))->getJson('/api/orders')->assertOk()->assertJsonPath('meta.total', 0);
        $this->actingAs(User::find($this->user->id))->getJson('/api/orders')->assertJsonPath('meta.total', 1);
    }

    public function test_impersonation_requires_a_reason_and_blocks_risky_edits(): void
    {
        $this->makeStore();
        $admin = User::create(['name' => 'অ্যাডমিন', 'phone' => '+8801911111111', 'password' => bcrypt('x'), 'is_admin' => true]);

        $this->actingAs(User::find($admin->id))->postJson("/admin/impersonate/{$this->user->id}", [])->assertStatus(422);

        $this->actingAs(User::find($admin->id))->postJson("/admin/impersonate/{$this->user->id}", ['reason' => 'সাপোর্ট কল'])
            ->assertOk();

        // the admin is now acting as the customer, and high-risk edits are blocked
        $this->assertAuthenticatedAs($this->user);
        $this->patchJson('/api/profile', ['name' => 'বদলানো'])->assertStatus(403);
    }
}
