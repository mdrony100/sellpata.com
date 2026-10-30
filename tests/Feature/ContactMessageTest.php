<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('contact');
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'রুমানা আক্তার',
            'phone' => '01712345678',
            'page_url' => 'https://facebook.com/roopkotha',
            'message' => 'আমার কুর্তির জন্য একটা ল্যান্ডিং পেজ চাই।',
        ], $overrides);
    }

    public function test_it_stores_a_valid_message_and_reports_success(): void
    {
        $response = $this->postJson(route('api.contact.store'), $this->validPayload());

        $response->assertCreated()->assertJson(['ok' => true]);

        $this->assertSame(1, ContactMessage::count());

        $message = ContactMessage::first();
        $this->assertSame('রুমানা আক্তার', $message->name);
        $this->assertSame('01712345678', $message->phone);
        $this->assertSame('https://facebook.com/roopkotha', $message->page_url);
        $this->assertStringContainsString('ধন্যবাদ', $response->json('message'));
    }

    public function test_it_accepts_the_country_code_and_normalises_spacing(): void
    {
        $this->postJson(route('api.contact.store'), $this->validPayload(['phone' => '+880 1712-345678']))
            ->assertCreated();

        $this->assertSame('+8801712345678', ContactMessage::first()->phone);
    }

    public function test_it_rejects_an_invalid_phone_number(): void
    {
        $this->postJson(route('api.contact.store'), $this->validPayload(['phone' => '12345']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_it_rejects_an_empty_name_and_message(): void
    {
        $this->postJson(route('api.contact.store'), ['name' => '', 'phone' => '01712345678', 'message' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'message']);
    }

    public function test_it_rejects_a_malformed_page_url_but_allows_an_empty_one(): void
    {
        $this->postJson(route('api.contact.store'), $this->validPayload(['page_url' => 'facebook.com/page']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('page_url');

        $this->postJson(route('api.contact.store'), $this->validPayload(['page_url' => null]))
            ->assertCreated();
    }

    public function test_it_returns_bengali_validation_messages(): void
    {
        $response = $this->postJson(route('api.contact.store'), $this->validPayload(['phone' => 'abc']));

        $this->assertStringContainsString('মোবাইল', $response->json('errors.phone.0'));
    }

    public function test_it_throttles_repeated_submissions(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson(route('api.contact.store'), $this->validPayload())->assertCreated();
        }

        $this->postJson(route('api.contact.store'), $this->validPayload())->assertStatus(429);
    }

    #[DataProvider('marketingRoutes')]
    public function test_it_serves_marketing_routes_for_direct_navigation(string $path): void
    {
        $this->get($path)->assertOk()->assertSee('window.__SELLPATA__', false);
    }

    public static function marketingRoutes(): array
    {
        return [['/'], ['/clients'], ['/contact']];
    }

    public function test_it_returns_404_for_an_unknown_path(): void
    {
        $this->get('/this-page-does-not-exist')->assertNotFound();
    }
}
