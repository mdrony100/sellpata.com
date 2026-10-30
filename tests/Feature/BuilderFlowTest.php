<?php

namespace Tests\Feature;

use App\Models\AnonymousIdentity;
use App\Models\BuilderTemplate;
use App\Models\Draft;
use App\Models\OtpChallenge;
use App\Models\User;
use App\Support\VisitorIdentity;
use Database\Seeders\BuilderContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BuilderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected BuilderTemplate $template;
    protected string $raw;
    protected AnonymousIdentity $identity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BuilderContentSeeder::class);
        $this->template = BuilderTemplate::firstOrFail();
        config(['otp.expose_test_code' => true]);

        $this->raw = Str::random(64);
        $this->identity = AnonymousIdentity::create([
            'token_hash' => VisitorIdentity::hash($this->raw),
            'expires_at' => now()->addDays(30),
            'last_seen_at' => now(),
        ]);
    }

    /** Sends a request with the anonymous cookie attached (json() drops cookies). */
    private function send(string $method, string $uri, array $payload = [], ?string $raw = null)
    {
        return $this->call(
            $method,
            $uri,
            [],
            ['sp_visitor' => $raw ?? $this->raw],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_UNICODE),
        );
    }

    private function createDraft(): array
    {
        $response = $this->send('POST', '/api/drafts', ['template_id' => $this->template->id]);
        $response->assertCreated();

        return [$response->json('draft'), Draft::latest('id')->first()];
    }

    public function test_selecting_a_template_creates_a_private_draft_copying_template_content(): void
    {
        $draft = $this->createDraft()[1];

        $this->assertSame($this->identity->id, $draft->anonymous_identity_id);
        $this->assertNull($draft->user_id);
        $this->assertSame('draft', $draft->status);
        $this->assertSame($this->template->content['hero']['headline'], $draft->content['hero']['headline']);
        $this->assertNotEmpty($draft->public_id);
        $this->assertSame(1, $draft->revision);
    }

    public function test_a_draft_is_private_to_the_browser_that_created_it(): void
    {
        [, $draft] = $this->createDraft();

        $this->send('GET', "/api/drafts/{$draft->public_id}")->assertOk();

        // Another browser, even knowing the URL, sees nothing.
        $this->send('GET', "/api/drafts/{$draft->public_id}", [], 'some-other-browser-token')->assertNotFound();
    }

    public function test_selecting_the_same_template_twice_does_not_duplicate_the_draft(): void
    {
        $first = $this->createDraft()[1];
        $response = $this->send('POST', '/api/drafts', ['template_id' => $this->template->id]);
        $response->assertOk();                       // reuses instead of creating
        $second = Draft::latest('id')->first();

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Draft::count());
    }

    public function test_autosave_stores_content_and_bumps_the_revision(): void
    {
        [, $draft] = $this->createDraft();

        $response = $this->send('PATCH', "/api/drafts/{$draft->public_id}", [
            'revision' => 1,
            'content' => ['hero' => ['headline' => 'নতুন শিরোনাম'], 'brand' => ['name' => 'আমার ব্র্যান্ড']],
        ])->assertOk();

        $this->assertSame(2, $draft->fresh()->revision);
        $this->assertSame('নতুন শিরোনাম', $draft->fresh()->content['hero']['headline']);
    }

    public function test_autosave_rejects_stale_revisions_instead_of_overwriting(): void
    {
        [, $draft] = $this->createDraft();

        $this->send('PATCH', "/api/drafts/{$draft->public_id}", [
            'revision' => 1,
            'content' => ['hero' => ['headline' => 'প্রথম ট্যাব']],
        ])->assertOk();

        // A second tab still believes it is on revision 1.
        $this->send('PATCH', "/api/drafts/{$draft->public_id}", [
            'revision' => 1,
            'content' => ['hero' => ['headline' => 'পুরনো ট্যাব']],
        ])->assertStatus(409);

        $this->assertSame('প্রথম ট্যাব', $draft->fresh()->content['hero']['headline']);
    }

    public function test_wrong_otp_codes_are_counted_and_lock_the_challenge(): void
    {
        [, $draft] = $this->createDraft();

        $send = $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678'])->assertOk();
        $this->assertStringContainsString('678', $send->json('phone_masked'));

        config(['otp.max_attempts' => 2]);
        $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => '000000'])->assertStatus(422);
        $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => '000000'])->assertStatus(422);

        $challenge = OtpChallenge::latest('id')->first();
        $this->assertSame(2, $challenge->attempts);
        $this->assertNull($challenge->consumed_at);
    }

    public function test_an_expired_code_is_rejected(): void
    {
        [, $draft] = $this->createDraft();
        $code = $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678'])->json('test_code');

        OtpChallenge::query()->update(['expires_at' => now()->subMinute()]);

        $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => $code])
            ->assertStatus(422);
    }

    public function test_resending_a_code_invalidates_the_previous_one(): void
    {
        [, $draft] = $this->createDraft();
        $first = $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678'])->json('test_code');

        config(['otp.resend_cooldown_seconds' => 0]);
        $second = $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678'])->json('test_code');

        if ($first !== $second) {
            $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => $first])
                ->assertStatus(422);
        }
        $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => $second])
            ->assertOk();
    }

    public function test_a_verified_code_cannot_be_reused(): void
    {
        [, $draft] = $this->createDraft();
        $code = $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678'])->json('test_code');

        $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => $code])->assertOk();
        $this->assertSame(1, OtpChallenge::whereNotNull('consumed_at')->count());

        $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => $code])->assertStatus(422);
    }

    public function test_verification_transfers_the_draft_and_removes_anonymous_access(): void
    {
        [, $draft] = $this->createDraft();
        $code = $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678'])->json('test_code');

        $response = $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => $code]);
        $response->assertOk();

        $draft->refresh();
        $user = User::query()->where('phone', 'like', '%1712345678')->firstOrFail();

        $this->assertSame($user->id, $draft->user_id);
        $this->assertNull($draft->anonymous_identity_id);
        $this->assertNotNull($draft->claimed_at);
        $this->assertSame('draft', $draft->status);        // nothing is published
        $this->assertSame(1, User::count());

        // A browser that still only holds the old anonymous token, and is not
        // signed in, no longer reaches the draft.
        \Illuminate\Support\Facades\Auth::logout();
        $this->flushSession();
        $this->send('GET', "/api/drafts/{$draft->public_id}")->assertNotFound();
    }

    public function test_an_existing_account_keeps_its_older_drafts_when_claiming_a_new_one(): void
    {
        $user = User::factory()->create(['phone' => '+8801712345678', 'phone_verified_at' => now()]);
        Draft::create([
            'public_id' => (string) Str::uuid(),
            'builder_template_id' => $this->template->id,
            'user_id' => $user->id,
            'content' => ['brand' => ['name' => 'পুরনো']],
            'revision' => 3,
            'title' => 'পুরনো ড্রাফট',
        ]);

        [, $draft] = $this->createDraft();
        $code = $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678'])->json('test_code');
        $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => $code])->assertOk();

        $this->assertSame(2, Draft::where('user_id', $user->id)->count());
        $this->assertSame(1, User::count());
    }

    public function test_the_demo_page_is_noindex_and_never_exposes_drafts(): void
    {
        [, $draft] = $this->createDraft();

        $response = $this->get("/demo/{$this->template->slug}");
        $response->assertOk();
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->assertStringNotContainsString($draft->public_id, $response->getContent());
    }

    public function test_demo_code_mode_works_in_production_when_explicitly_enabled(): void
    {
        [$payload, $draft] = $this->createDraft();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->app['env'] = 'production';
        config(['otp.expose_test_code' => true, 'otp.driver' => 'log']);

        $sent = $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678']);
        $sent->assertOk();
        $code = $sent->json('test_code');
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $code);

        $this->send('POST', "/api/drafts/{$draft->public_id}/otp/verify", ['phone' => '01712345678', 'code' => $code])
            ->assertOk();
        $this->assertNotNull(User::where('phone', '+8801712345678')->first());
    }

    public function test_production_without_demo_flag_refuses_and_hides_the_code(): void
    {
        [$payload, $draft] = $this->createDraft();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->app['env'] = 'production';
        config(['otp.expose_test_code' => false, 'otp.driver' => 'log']);

        $this->send('POST', "/api/drafts/{$draft->public_id}/otp", ['phone' => '01712345678'])
            ->assertStatus(503)
            ->assertJsonMissing(['test_code']);
    }
}
