<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\ContentSeeder::class);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('secret-pass'),
            'is_admin' => true,
        ]);
    }

    public function test_guests_are_redirected_from_the_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/templates')->assertRedirect('/admin/login');
    }

    public function test_a_non_admin_user_is_forbidden(): void
    {
        $user = User::create([
            'name' => 'Visitor',
            'email' => 'visitor@example.test',
            'password' => Hash::make('secret-pass'),
            'is_admin' => false,
        ]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_an_admin_can_log_in_and_open_the_dashboard(): void
    {
        $this->admin();

        $this->post('/admin/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-pass',
        ])->assertRedirect('/admin');

        $this->actingAs(User::first())->get('/admin')->assertOk()->assertSee('ড্যাশবোর্ড');
    }

    public function test_editing_a_template_changes_the_public_bootstrap(): void
    {
        $template = Template::firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.templates.update', $template), [
                'category_id' => $template->category_id,
                'brand' => 'পরীক্ষা ব্র্যান্ড',
                'headline' => 'নতুন হেডলাইন',
                'art' => 'fashion',
                'palette' => ['bg' => '#fff', 'surface' => '#fff', 'ink' => '#111', 'primary' => '#155e47', 'accent' => '#c9f24d'],
                'is_published' => true,
            ])->assertRedirect(route('admin.templates.index'));

        $this->assertDatabaseHas('templates', ['brand' => 'পরীক্ষা ব্র্যান্ড']);
        $this->get('/')->assertSee('পরীক্ষা ব্র্যান্ড', false);
    }

    public function test_settings_are_stored_and_used_by_the_public_site(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), [
                'brand' => ['name' => 'SellPata', 'domain' => 'sellpata.com', 'tagline' => 'টেস্ট ট্যাগলাইন'],
                'youtube_url' => 'https://www.youtube.com/watch?v=abc12345678',
                'template_count_label' => '',
                'features' => ['payments' => ['available' => '1'], 'shipping' => ['available' => '0'], 'coming_soon_label' => 'শিগগিরই আসছে'],
                'contact' => ['phone' => '01712345678', 'email' => '', 'whatsapp' => ''],
            ])->assertSessionHas('status');

        $this->assertDatabaseHas('settings', ['key' => 'contact.phone', 'value' => '01712345678']);
        $this->assertDatabaseHas('settings', ['key' => 'features.payments.available', 'value' => '1']);

        $this->get('/')->assertSee('টেস্ট ট্যাগলাইন', false)->assertSee('abc12345678', false);
    }

    public function test_contact_messages_appear_in_the_admin_panel(): void
    {
        ContactMessage::create([
            'name' => 'রুমানা',
            'phone' => '01712345678',
            'message' => 'ল্যান্ডিং পেজ দরকার',
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/messages')
            ->assertOk()
            ->assertSee('রুমানা', false)
            ->assertSee('ল্যান্ডিং পেজ দরকার', false);
    }
}
