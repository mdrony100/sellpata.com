<?php

namespace Tests\Feature;

use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['otp.expose_test_code' => true]);
        User::create(['name' => 'রুমানা', 'phone' => '+8801711000001', 'password' => bcrypt('x')]);
    }

    public function test_a_customer_signs_in_with_a_phone_code(): void
    {
        $code = $this->postJson('/api/auth/send-otp', ['phone' => '+8801711000001'])
            ->assertOk()
            ->json('test_code');

        $this->postJson('/api/auth/verify', ['phone' => '+8801711000001', 'code' => '000000'])->assertStatus(422);
        $this->assertGuest();

        $this->postJson('/api/auth/verify', ['phone' => '+8801711000001', 'code' => $code])
            ->assertOk()
            ->assertJsonPath('redirect', '/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login']);
    }

    public function test_an_unknown_number_looks_the_same_and_gets_no_code(): void
    {
        $response = $this->postJson('/api/auth/send-otp', ['phone' => '+8801999999999'])->assertOk();

        $this->assertTrue($response->json('ok'));
        $this->assertNull($response->json('test_code'));
        $this->assertSame(0, OtpChallenge::where('purpose', 'login')->count());
    }
}
