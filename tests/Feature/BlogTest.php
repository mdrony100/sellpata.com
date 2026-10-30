<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function publish(array $attributes = []): Post
    {
        return Post::create(array_merge([
            'title' => 'ফেসবুক পেজ থেকে অর্ডার নেওয়ার উপায়',
            'body' => '<p>প্রথম ধাপ</p>',
            'is_published' => true,
            'meta_title' => 'ফেসবুক পেজ থেকে অর্ডার',
        ], $attributes));
    }

    public function test_only_published_posts_appear_in_the_blog_feed(): void
    {
        $live = $this->publish();
        $this->publish(['title' => 'খসড়া লেখা', 'is_published' => false]);

        $this->getJson('/api/blog')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.slug', $live->slug);
    }

    public function test_a_published_post_renders_with_its_own_seo_tags(): void
    {
        $post = $this->publish();

        $this->get('/blog/'.$post->slug)
            ->assertOk()
            ->assertSee('ফেসবুক পেজ থেকে অর্ডার', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_a_draft_is_not_reachable(): void
    {
        $post = $this->publish(['is_published' => false]);

        $this->get('/blog/'.$post->slug)->assertNotFound();
        $this->getJson('/api/blog/'.$post->slug)->assertNotFound();
    }

    public function test_the_sitemap_lists_published_posts(): void
    {
        $post = $this->publish();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/blog/'.$post->slug, false);
    }

    public function test_legal_pages_read_from_settings(): void
    {
        \App\Models\Setting::updateOrCreate(['key' => 'legal.privacy.body'], ['value' => '<p>তথ্য সংগ্রহ</p>']);

        $this->get('/privacy-policy')->assertOk();
        $this->getJson('/api/legal/privacy')->assertOk()->assertJsonPath('body', '<p>তথ্য সংগ্রহ</p>');
        $this->get('/terms')->assertOk();
    }

    public function test_an_admin_can_save_site_and_blog_seo(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'seo@example.com',
            'password' => bcrypt('secret'), 'is_admin' => true,
        ]);

        $this->actingAs($admin)->put('/admin/seo', [
            'seo.site.title' => 'সেলপাতা — ল্যান্ডিং পেজ',
            'seo.blog.title' => 'ব্লগ',
            'seo.robots' => 'index,follow',
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key' => 'seo.site.title',
            'value' => 'সেলপাতা — ল্যান্ডিং পেজ',
        ]);
        $this->assertDatabaseHas('settings', ['key' => 'seo.blog.title', 'value' => 'ব্লগ']);
    }

    public function test_an_admin_can_write_a_post(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'writer@example.com',
            'password' => bcrypt('secret'), 'is_admin' => true,
        ]);

        $this->actingAs($admin)->post('/admin/posts', [
            'title' => 'নতুন লেখা',
            'body' => '<p>বিষয়বস্তু</p>',
            'is_published' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('posts', ['title' => 'নতুন লেখা', 'is_published' => true]);
    }
}
