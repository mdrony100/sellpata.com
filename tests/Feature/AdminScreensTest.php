<?php

namespace Tests\Feature;

use App\Models\BuilderTemplate;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\BuilderContentSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScreensTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): array
    {
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);

        $user = User::create(['name' => 'রুমানা', 'phone' => '+8801712345678', 'password' => bcrypt('x')]);
        $this->actingAs($user)->postJson('/api/store', ['username' => 'organicfood'])->assertSuccessful();
        $store = Store::firstOrFail();

        $template = BuilderTemplate::firstOrFail();
        $page = $store->pages()->create([
            'template_id' => $template->id, 'title' => 'আম', 'slug' => 'mango', 'status' => 'draft',
            'content' => $template->content, 'revision' => 1,
        ]);
        $page->forceFill(['status' => 'published', 'published_content' => $page->content, 'is_primary' => true, 'published_at' => now()])->save();

        return [$user->fresh(), $store];
    }

    public function test_admin_screens_render_for_admins(): void
    {
        [$customer] = $this->customer();
        $admin = User::create(['name' => 'অ্যাডমিন', 'phone' => '+8801911111111', 'password' => bcrypt('x'), 'is_admin' => true]);

        $this->actingAs($admin)->get('/admin/customers')->assertOk()->assertSee('গ্রাহক ও স্টোর');
        $this->actingAs($admin)->get('/admin/customers/'.$customer->id)->assertOk()->assertSee('প্ল্যান নির্ধারণ');
        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee('সব অর্ডার');
        $this->actingAs($admin)->get('/admin/plans')->assertOk()->assertSee('প্যাকেজ ও সীমা');
        $this->actingAs($admin)->get('/admin/audit')->assertOk()->assertSee('অডিট ও মনিটরিং');
    }

    public function test_customers_cannot_open_admin_screens(): void
    {
        [$customer] = $this->customer();

        $response = $this->actingAs($customer)->get('/admin/customers');
        $this->assertContains($response->getStatusCode(), [302, 403], "customer must not reach the admin area");
    }

    public function test_admin_can_suspend_a_store_with_a_reason(): void
    {
        [, $store] = $this->customer();
        $admin = User::create(['name' => 'অ্যাডমিন', 'phone' => '+8801911111111', 'password' => bcrypt('x'), 'is_admin' => true]);

        $this->actingAs($admin)->post("/admin/customers/{$store->id}/suspend", ['reason' => 'নকল পণ্য'])->assertRedirect();

        $this->assertSame('suspended', $store->fresh()->status);
        $this->assertSame('নকল পণ্য', $store->fresh()->suspended_reason);
        $this->assertDatabaseHas('audit_logs', ['action' => 'store.suspended']);

        // a suspended store stops selling but keeps its data
        $this->get('http://organicfood.sellpata.com/')->assertStatus(503);
        $this->assertSame(1, $store->pages()->count());
    }

    public function test_order_invoice_downloads_as_a_real_pdf(): void
    {
        [$customer, $store] = $this->customer();

        $this->postJson('http://organicfood.sellpata.com/checkout', [
            'customer_name' => 'করিম', 'customer_phone' => '01712345678', 'address' => 'মিরপুর, ঢাকা',
        ])->assertCreated();

        $order = $store->orders()->firstOrFail();

        $response = $this->actingAs($customer)->get("/api/orders/{$order->id}/invoice");
        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertGreaterThan(5000, strlen($response->getContent()));
    }
}
