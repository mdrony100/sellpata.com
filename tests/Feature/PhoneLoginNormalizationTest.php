<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneLoginNormalizationTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::create([
            'name' => 'Rony',
            'phone' => '+8801757571122',
            'password' => bcrypt('secret'),
        ]);
    }

    public static function localFormats(): array
    {
        return [
            'with leading zero' => ['01757571122'],
            'with 88 prefix' => ['8801757571122'],
            'with plus' => ['+8801757571122'],
            'with plus and spaces' => ['+880 1757-571122'],
            'bare national number' => ['1757571122'],
        ];
    }

    /**
     * @dataProvider localFormats
     */
    public function test_every_way_of_typing_the_same_number_reaches_the_account(string $input): void
    {
        $this->customer();

        $response = $this->postJson('/api/auth/send-otp', ['phone' => $input])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertArrayHasKey('test_code', $response->json(), 'the demo code must be returned so the field can prefill');
        $this->assertDatabaseHas('otp_challenges', ['phone' => '+8801757571122', 'purpose' => 'login']);
    }

    public function test_a_code_sent_to_a_local_number_verifies_and_logs_in(): void
    {
        $user = $this->customer();

        $code = $this->postJson('/api/auth/send-otp', ['phone' => '01757571122'])->json('test_code');

        $this->postJson('/api/auth/verify', ['phone' => '01757571122', 'code' => $code])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_number_that_is_not_a_phone_is_rejected(): void
    {
        $this->postJson('/api/auth/send-otp', ['phone' => '12345'])->assertStatus(422);
        $this->postJson('/api/auth/send-otp', ['phone' => 'abcd'])->assertStatus(422);
    }

    public function test_an_unknown_number_still_answers_the_same_way_without_a_code(): void
    {
        $response = $this->postJson('/api/auth/send-otp', ['phone' => '01999999999'])->assertOk();

        $this->assertArrayNotHasKey('test_code', $response->json());
        $this->assertDatabaseCount('otp_challenges', 0);
    }
}
