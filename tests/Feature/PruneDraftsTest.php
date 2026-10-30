<?php

namespace Tests\Feature;

use App\Models\AnonymousIdentity;
use App\Models\BuilderTemplate;
use App\Models\Draft;
use App\Models\User;
use App\Support\VisitorIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneDraftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prunes_old_unclaimed_drafts_and_keeps_claimed_ones(): void
    {
        Storage::fake('local');
        $this->seed(\Database\Seeders\BuilderContentSeeder::class);
        $template = BuilderTemplate::firstOrFail();

        $identity = AnonymousIdentity::create([
            'token_hash' => VisitorIdentity::hash('stale'),
            'expires_at' => now()->subDay(),
        ]);

        $stale = Draft::create([
            'builder_template_id' => $template->id,
            'anonymous_identity_id' => $identity->id,
            'title' => 'পুরোনো ড্রাফট',
            'content' => $template->content,
            'revision' => 1,
            'status' => 'draft',
        ]);
        $stale->forceFill(['updated_at' => now()->subDays(45)])->save();
        $stale->media()->create([
            'field' => 'product.image',
            'path' => "drafts/{$stale->id}/a.png",
            'original_name' => 'chobi.png',
            'mime' => 'image/png',
            'size' => 12,
        ]);
        Storage::disk('local')->put("drafts/{$stale->id}/a.png", 'x');

        $user = User::create(['name' => 'মালিক', 'password' => bcrypt('secret')]);
        $claimed = Draft::create([
            'builder_template_id' => $template->id,
            'user_id' => $user->id,
            'title' => 'সংরক্ষিত ড্রাফট',
            'content' => $template->content,
            'revision' => 1,
            'status' => 'draft',
        ]);
        $claimed->forceFill(['updated_at' => now()->subDays(90)])->save();

        $this->artisan('drafts:prune')->assertSuccessful();

        $this->assertDatabaseMissing('drafts', ['id' => $stale->id]);
        $this->assertDatabaseMissing('draft_media', ['draft_id' => $stale->id]);
        Storage::disk('local')->assertMissing("drafts/{$stale->id}/a.png");
        $this->assertDatabaseHas('drafts', ['id' => $claimed->id]);
        $this->assertDatabaseMissing('anonymous_identities', ['id' => $identity->id]);
    }
}
