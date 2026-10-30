<?php

namespace Tests\Feature;

use App\Models\OtpChallenge;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\BuilderContentSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneChangeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);
        config(['otp.expose_test_code' => true]);

        $this->user = User::create(['name' => 'রুমানা', 'phone' => '+8801712345678', 'password' => bcrypt('x')]);
        $this->actingAs($this->user)->postJson('/api/store', ['username' => 'organicfood'])->assertSuccessful();
    }

    public function test_changing_the_login_number_needs_the_code(): void
    {
        $sent = $this->actingAs($this->user)->postJson('/api/profile/phone/send-otp', ['phone' => '01812345678'])
            ->assertOk();
        $code = $sent->json('test_code');
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $code);

        // the live number does not move before verification
        $this->assertSame('+8801712345678', $this->user->fresh()->phone);

        $this->actingAs($this->user)->postJson('/api/profile/phone/verify', ['phone' => '01812345678', 'code' => '000000'])
            ->assertStatus(422);
        $this->assertSame('+8801712345678', $this->user->fresh()->phone);

        $this->actingAs($this->user)->postJson('/api/profile/phone/verify', ['phone' => '01812345678', 'code' => $code])
            ->assertOk();

        $this->assertSame('01812345678', $this->user->fresh()->phone);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.phone_changed']);
        $this->assertNotNull(OtpChallenge::where('user_id', $this->user->id)->first()->consumed_at);
    }

    public function test_a_code_cannot_be_reused(): void
    {
        $code = $this->actingAs($this->user)->postJson('/api/profile/phone/send-otp', ['phone' => '01812345678'])
            ->json('test_code');

        $this->actingAs($this->user)->postJson('/api/profile/phone/verify', ['phone' => '01812345678', 'code' => $code])->assertOk();

        // replaying the same code for a second change must fail
        $this->actingAs($this->user)->postJson('/api/profile/phone/verify', ['phone' => '01912345678', 'code' => $code])
            ->assertStatus(422);
    }

    public function test_a_number_belonging_to_someone_else_is_refused(): void
    {
        User::create(['name' => 'অন্য', 'phone' => '01799999999', 'password' => bcrypt('x')]);

        $this->actingAs($this->user)->postJson('/api/profile/phone/send-otp', ['phone' => '01799999999'])
            ->assertStatus(422);
    }

    public function test_downloads_lists_invoices_and_respects_the_plan(): void
    {
        $store = Store::firstOrFail();

        $free = $this->actingAs($this->user)->getJson('/api/downloads')->assertOk();
        $this->assertFalse($free->json('csv_export'));

        $store->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);
        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => Plan::where('slug', 'starter')->firstOrFail()->id,
            'status' => 'active', 'source' => 'manual', 'started_at' => now(),
        ]);

        $invoice = app(\App\Services\Platform\InvoiceService::class)
            ->createSubscriptionInvoice($store->fresh()->activeSubscription(), 990, 'unpaid');

        $paid = $this->actingAs($this->user)->getJson('/api/downloads')->assertOk();
        $this->assertTrue($paid->json('csv_export'));
        $this->assertSame($invoice->number, $paid->json('invoices.0.number'));
        $this->assertSame('সাবস্ক্রিপশন', $paid->json('invoices.0.type_label'));
    }
}
