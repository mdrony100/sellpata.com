<?php

namespace Tests\Feature;

use App\Models\BuilderTemplate;
use App\Models\Page;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\BuilderContentSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * OTP login regenerates the session. The next write must use the new CSRF
 * secret, not the token from the page that was open before login.
 */
class OtpSessionHandoffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);
        config(['otp.expose_test_code' => true, 'otp.skip_verification' => false]);

        $this->app->bind(ValidateCsrfToken::class, function ($app) {
            return new class($app, $app->make(Encrypter::class)) extends ValidateCsrfToken
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
    }

    public function test_otp_login_returns_a_fresh_csrf_token_and_the_draft_becomes_a_page(): void
    {
        $user = User::create([
            'name' => 'রুমানা',
            'phone' => '+8801711223344',
            'password' => bcrypt('x'),
            'phone_verified_at' => now(),
        ]);
        $store = Store::create([
            'user_id' => $user->id,
            'username' => 'rumana',
            'username_lower' => 'rumana',
            'display_name' => 'রুমানার দোকান',
        ]);
        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => Plan::free()->id,
            'status' => 'active',
            'source' => 'free',
            'started_at' => now(),
        ]);

        $this->startSession();
        $stale = 'stale-token-from-the-open-page';
        session()->put('_token', $stale);
        $this->withCredentials();
        $this->withCookie(config('session.cookie'), session()->getId());

        $template = BuilderTemplate::where('slug', 'prakritik')->firstOrFail();

        $created = $this->postJson('/api/drafts', ['template_id' => $template->id], [
            'X-CSRF-TOKEN' => $stale,
        ]);
        $created->assertCreated();
        $this->carryCookies($created);
        $draftId = $created->json('draft.id');

        $sent = $this->postJson("/api/drafts/{$created->json('draft.public_id')}/otp", [
            'phone' => '01711223344',
        ], ['X-CSRF-TOKEN' => $stale]);
        $sent->assertOk();
        $this->carryCookies($sent);

        $verified = $this->postJson("/api/drafts/{$created->json('draft.public_id')}/otp/verify", [
            'phone' => '01711223344',
            'code' => (string) $sent->json('test_code'),
        ], ['X-CSRF-TOKEN' => $stale]);
        $verified->assertOk();

        $fresh = $verified->json('csrf');
        $this->assertIsString($fresh);
        $this->assertNotSame($stale, $fresh);
        $this->carryCookies($verified);

        $this->postJson('/api/pages', ['draft_id' => $draftId], [
            'X-CSRF-TOKEN' => $stale,
        ])->assertStatus(419);

        $this->postJson('/api/pages', ['draft_id' => $draftId], [
            'X-CSRF-TOKEN' => $fresh,
        ])->assertCreated();

        $this->assertSame(1, Page::where('store_id', $store->id)->count());
        $this->assertSame($template->id, Page::where('store_id', $store->id)->first()->template_id);
    }

    public function test_demo_payload_includes_the_template_id_used_to_start_a_draft(): void
    {
        $this->getJson('/api/demo/prakritik')
            ->assertOk()
            ->assertJsonPath('slug', 'prakritik')
            ->assertJsonPath('id', BuilderTemplate::where('slug', 'prakritik')->value('id'));
    }

    private function carryCookies(TestResponse $response): void
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === 'sp_visitor') {
                $this->withUnencryptedCookie('sp_visitor', $cookie->getValue());

                continue;
            }

            $plain = $response->getCookie($cookie->getName());
            if ($plain) {
                $this->withCookie($plain->getName(), $plain->getValue());
            }
        }
    }
}
