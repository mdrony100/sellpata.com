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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);
    }

    public function test_switching_theme_keeps_merchant_content_and_a_custom_section_order(): void
    {
        $user = $this->merchant();
        $prakritik = BuilderTemplate::where('slug', 'prakritik')->firstOrFail();
        $smart = BuilderTemplate::where('slug', 'smart')->firstOrFail();

        $content = $prakritik->content;
        $content['product']['name'] = 'আমার মধু';
        $content['product']['sale_price'] = 1234;
        $content['section_order'] = ['hero', 'faq', 'order'];
        $content['sections']['gallery'] = false;

        $page = Page::create([
            'store_id' => $user->store->id,
            'template_id' => $prakritik->id,
            'title' => 'আমার মধু',
            'slug' => 'modhu',
            'status' => 'published',
            'is_primary' => true,
            'content' => $content,
            'published_content' => $content,
            'published_at' => now(),
            'revision' => 1,
        ]);

        $this->actingAs($user)->postJson("/api/pages/{$page->id}/theme", [
            'template_id' => $smart->id,
            'confirm' => true,
            'republish' => false,
        ])->assertOk()->assertJsonPath('republished', false)->assertJsonPath('theme_pending', true);

        $page->refresh();
        $this->assertSame($smart->id, $page->template_id);
        $this->assertSame($prakritik->id, $page->published_template_id);
        $this->assertSame('আমার মধু', $page->content['product']['name']);
        $this->assertSame(1234, $page->content['product']['sale_price']);
        $this->assertSame(['hero', 'faq', 'order'], $page->content['section_order']);
        $this->assertFalse($page->content['sections']['gallery']);
        $this->assertSame('video', $page->content['hero']['mode']);
        $this->assertSame('split', $page->published_content['hero']['mode']);
        $this->assertSame('prakritik', $page->liveDesign());

        $this->actingAs($user)->postJson("/api/pages/{$page->id}/theme", [
            'template_id' => $smart->id,
            'confirm' => true,
            'republish' => true,
        ])->assertOk()->assertJsonPath('republished', true);

        $page->refresh();
        $this->assertSame($smart->id, $page->published_template_id);
        $this->assertSame('আমার মধু', $page->published_content['product']['name']);
        $this->assertSame(1234, $page->published_content['product']['sale_price']);
        $this->assertSame(['hero', 'faq', 'order'], $page->published_content['section_order']);
        $this->assertSame('smart', $page->liveDesign());
    }

    public function test_an_untouched_section_order_takes_the_new_theme_default(): void
    {
        $user = $this->merchant();
        $prakritik = BuilderTemplate::where('slug', 'prakritik')->firstOrFail();
        $smart = BuilderTemplate::where('slug', 'smart')->firstOrFail();
        $content = $prakritik->content;
        $content['faq'][0]['q'] = 'আমার প্রশ্ন?';

        $page = Page::create([
            'store_id' => $user->store->id,
            'template_id' => $prakritik->id,
            'title' => 'মধু',
            'slug' => 'honey',
            'status' => 'draft',
            'content' => $content,
            'revision' => 1,
        ]);

        $this->actingAs($user)->postJson("/api/pages/{$page->id}/theme", [
            'template_id' => $smart->id,
            'confirm' => true,
        ])->assertOk();

        $page->refresh();
        $this->assertSame($smart->content['section_order'], $page->content['section_order']);
        $this->assertSame($smart->content['hero']['mode'], $page->content['hero']['mode']);
        $this->assertSame('আমার প্রশ্ন?', $page->content['faq'][0]['q']);
    }

    public function test_unknown_and_inactive_themes_are_rejected(): void
    {
        $user = $this->merchant();
        $prakritik = BuilderTemplate::where('slug', 'prakritik')->firstOrFail();
        $spotlight = BuilderTemplate::where('design', 'spotlight')->firstOrFail();
        $page = Page::create([
            'store_id' => $user->store->id,
            'template_id' => $prakritik->id,
            'title' => 'মধু',
            'slug' => 'honey',
            'status' => 'draft',
            'content' => $prakritik->content,
            'revision' => 1,
        ]);

        $this->actingAs($user)->postJson("/api/pages/{$page->id}/theme", [
            'template_id' => $spotlight->id,
            'confirm' => true,
        ])->assertStatus(422);

        $this->actingAs($user)->postJson("/api/pages/{$page->id}/theme", [
            'template_id' => 999999,
            'confirm' => true,
        ])->assertStatus(422);

        $this->actingAs($user)->postJson("/api/pages/{$page->id}/theme", [
            'template_id' => BuilderTemplate::where('slug', 'komol')->value('id'),
        ])->assertStatus(422);

        $this->assertSame($prakritik->id, $page->fresh()->template_id);
    }

    private function merchant(): User
    {
        $user = User::create([
            'name' => 'বিক্রেতা',
            'phone' => '+88017'.random_int(10000000, 99999999),
            'password' => bcrypt('x'),
            'phone_verified_at' => now(),
        ]);
        $store = Store::create([
            'user_id' => $user->id,
            'username' => 'shop'. $user->id,
            'username_lower' => 'shop'.$user->id,
            'display_name' => 'দোকান',
        ]);
        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => Plan::free()->id,
            'status' => 'active',
            'source' => 'free',
            'started_at' => now(),
        ]);

        return $user->fresh('store');
    }
}
