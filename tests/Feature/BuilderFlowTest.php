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
use Illuminate\Support\Str;
use Tests\TestCase;

class BuilderFlowTest extends TestCase
{
    use RefreshDatabase;

    private BuilderTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BuilderContentSeeder::class);
        $this->template = BuilderTemplate::firstOrFail();

        config([
            'otp.driver' => 'log',
            'otp.expose_test_code' => true,
            'otp.resend_cooldown_seconds' => 0,
            'otp.max_sends_per_hour' => 20,
        ]);

        $this->actAsVisitor();
    }

    private function actAsVisitor(string $raw = 'test-visitor-token'): AnonymousIdentity
    {
        $identity = AnonymousIdentity::create([
            'token_hash' => VisitorIdentity::hash($raw),
            'expires_at' => now()->addDays(30),
            'last_seen_at' => now(),
        ]);

        $this->withUnencryptedCookie(VisitorIdentity::COOKIE, $raw);

        return $identity;
    }

    private function createDraft(): array
    {
        $response = $this->postJson('/api/drafts', ['template_id' => $this->template->id])
            ->assertCreated();

        return [$response->json('draft'), $response->json('draft.public_id')];
    }

    public function test_a_draft_is_private_to_the_browser_that_created_it(): void
    {
        [, $publicId] = $this->createDraft();

        $this->getJson("/api/drafts/{$publicId}")->assertOk();

        // A different browser (new cookie) must not see it.
        $this->withUnencryptedCookie(VisitorIdentity::COOKIE, 'some-other-browser-token');
        $this->getJson("/api/drafts/{$publicId}")->assertNotFound();
    }

    public function test_creating_twice_in_a_row_reuses_the_draft(): void
    {
        $first = $this->postJson('/api/drafts', ['template_id' => $this->template->id])->json('draft.public_id');
        $second = $this->postJson('/api/drafts', ['template_id' => $this->template->id])->json('draft.public_id');

        $this->assertSame($first, $second);
        $this->assertSame(1, Draft::count());
    }

    public function test_autosave_rejects_a_stale_revision_and_keeps_the_newer_content(): void
    {
        [$draft, $publicId] = $this->createDraft();

        $content = $draft['content'];
        $content['hero']['headline'] = 'নতুন শিরোনাম';

        $this->patchJson("/api/drafts/{$publicId}", ['content' => $content, 'revision' => 1])
            ->assertOk()
            ->assertJsonPath('revision', 2);

        // A second tab still holding revision 1 must not overwrite revision 2.
        $stale = $content;
        $stale['hero']['headline'] = 'পুরোনো ট্যাবের লেখা';

        $this->patchJson("/api/drafts/{$publicId}", ['content' => $stale, 'revision' => 1])
            ->assertStatus(409)
            ->assertJsonPath('conflict', true);

        $this->assertSame('নতুন শিরোনাম', Draft::first()->content['hero']['headline']);
    }

    public function test_otp_wrong_code_and_attempt_limit(): void
    {
        [, $publicId] = $this->createDraft();

        $this->postJson("/api/drafts/{$publicId}/otp", ['phone' => '01712345678'])->assertOk();

        $this->postJson("/api/drafts/{$publicId}/otp/verify", ['phone' => '01712345678', 'code' => '000000'])
            ->assertStatus(422)
            ->assertJsonPath('reason', 'invalid_code');

        $this->assertSame(1, OtpChallenge::first()->attempts);
    }

    public function test_expired_code_is_rejected(): void
    {
        [, $publicId] = $this->createDraft();

        $this->postJson("/api/drafts/{$publicId}/otp", ['phone' => '01712345678'])->assertOk();
        OtpChallenge::query()->update(['expires_at' => now()->subMinute()]);

        $this->postJson("/api/drafts/{$publicId}/otp/verify", ['phone' => '01712345678', 'code' => '123456'])
            ->assertStatus(422)
            ->assertJsonPath('reason', 'expired');
    }

    public function test_a_new_code_invalidates_the_previous_one(): void
    {
        [, $publicId] = $this->createDraft();

        $first = $this->postJson("/api/drafts/{$publicId}/otp", ['phone' => '01712345678'])->json('test_code');
        $second = $this->postJson("/api/drafts/{$publicId}/otp", ['phone' => '01712345678'])->json('test_code');

        $this->postJson("/api/drafts/{$publicId}/otp/verify", ['phone' => '01712345678', 'code' => $first])
            ->assertStatus(422);

        $this->postJson("/api/drafts/{$publicId}/otp/verify", ['phone' => '01712345678', 'code' => $second])
            ->assertOk();
    }

    public function test_successful_verification_creates_the_user_and_claims_the_draft(): void
    {
        [, $publicId] = $this->createDraft();
        $code = $this->postJson("/api/drafts/{$publicId}/otp", ['phone' => '+880 1712-345678'])->json('test_code');

        $this->postJson("/api/drafts/{$publicId}/otp/verify", ['phone' => '01712345678', 'code' => $code])
            ->assertOk();

        $draft = Draft::first();
        $this->assertNotNull($draft->user_id);
        $this->assertNull($draft->anonymous_identity_id);
        $this->assertSame('draft', $draft->status);
        $this->assertDatabaseHas('users', ['phone' => '+8801712345678']);
        $this->assertAuthenticated();
    }

    public function test_a_claimed_draft_is_no_longer_reachable_with_the_old_anonymous_cookie(): void
    {
        [, $publicId] = $this->createDraft();
        $code = $this->postJson("/api/drafts/{$publicId}/otp", ['phone' => '01712345678'])->json('test_code');
        $this->postJson("/api/drafts/{$publicId}/otp/verify", ['phone' => '01712345678', 'code' => $code])->assertOk();

        // Same browser, but signed out and back to the anonymous cookie only.
        auth()->logout();

        $this->getJson("/api/drafts/{$publicId}")->assertNotFound();
    }

    public function test_an_existing_user_keeps_their_other_drafts(): void
    {
        $user = User::create([
            'name' => 'পুরোনো ব্যবহারকারী',
            'phone' => '+8801712345678',
            'password' => bcrypt('secret'),
        ]);

        $older = Draft::create([
            'builder_template_id' => $this->template->id,
            'user_id' => $user->id,
            'title' => 'পুরোনো ড্রাফট',
            'content' => $this->template->content,
            'status' => 'draft',
        ]);

        [, $publicId] = $this->createDraft();
        $code = $this->postJson("/api/drafts/{$publicId}/otp", ['phone' => '01712345678'])->json('test_code');
        $this->postJson("/api/drafts/{$publicId}/otp/verify", ['phone' => '01712345678', 'code' => $code])->assertOk();

        $this->assertSame(2, Draft::where('user_id', $user->id)->count());
        $this->assertDatabaseCount('users', 1);
        $this->assertNotNull($older->fresh());
    }

    public function test_demo_payload_is_marked_noindex_and_never_contains_draft_content(): void
    {
        $response = $this->getJson('/api/demo/'.$this->template->slug)->assertOk();

        $this->assertStringContainsString('noindex', $response->headers->get('X-Robots-Tag') ?? '');
        $response->assertJsonMissingPath('draft');
    }
}
