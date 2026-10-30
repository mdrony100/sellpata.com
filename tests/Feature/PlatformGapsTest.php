<?php

namespace Tests\Feature;

use App\Models\BuilderTemplate;
use App\Models\Draft;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\BuilderContentSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformGapsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Store $store;
    private BuilderTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(BuilderContentSeeder::class);
        $this->template = BuilderTemplate::firstOrFail();

        $this->user = User::create(['name' => 'রুমানা', 'phone' => '+8801712345678', 'password' => bcrypt('x')]);
        $this->actingAs($this->user)->postJson('/api/store', ['username' => 'organicfood'])->assertSuccessful();
        $this->store = Store::firstOrFail();
    }

    private function page(string $slug, string $headline): \App\Models\Page
    {
        $page = $this->store->pages()->create([
            'template_id' => $this->template->id,
            'title' => $slug,
            'slug' => $slug,
            'status' => 'published',
            'content' => ['hero' => ['headline' => $headline], 'product' => ['name' => 'আম', 'price' => '৫০০']],
            'published_content' => ['hero' => ['headline' => $headline], 'product' => ['name' => 'আম', 'price' => '৫০০']],
            'published_at' => now(),
            'revision' => 1,
        ]);

        if (! $this->store->pages()->where('is_primary', true)->exists()) {
            $page->forceFill(['is_primary' => true])->save();
        }

        return $page;
    }

    private function givePlan(string $slug): void
    {
        $this->store->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);
        Subscription::create([
            'store_id' => $this->store->id,
            'plan_id' => Plan::where('slug', $slug)->firstOrFail()->id,
            'status' => 'active',
            'source' => 'manual',
            'started_at' => now(),
        ]);
    }

    public function test_old_slug_redirects_to_the_new_address(): void
    {
        $page = $this->page('mango', 'সেরা আম');

        $this->actingAs(User::find($this->user->id))
            ->patchJson("/api/pages/{$page->id}", ['slug' => 'aam'])
            ->assertOk();

        // the target is the primary page, whose canonical address is the store root
        $this->get('http://organicfood.sellpata.com/mango')
            ->assertStatus(301)
            ->assertRedirect('https://organicfood.sellpata.com');

        $this->get('http://organicfood.sellpata.com/aam')->assertOk();
    }

    public function test_editing_a_page_republishes_the_existing_page(): void
    {
        $page = $this->page('mango', 'পুরনো শিরোনাম');

        $draftId = $this->actingAs(User::find($this->user->id))
            ->postJson("/api/pages/{$page->id}/edit")
            ->assertOk()
            ->json('public_id');

        // builder autosave only touches the draft
        $draft = Draft::where('public_id', $draftId)->firstOrFail();
        $draft->forceFill(['content' => array_merge($draft->content, ['hero' => ['headline' => 'নতুন শিরোনাম']])])->save();

        $this->assertStringContainsString('পুরনো শিরোনাম', $this->get('http://organicfood.sellpata.com/mango')->getContent());

        $this->actingAs(User::find($this->user->id))
            ->postJson('/api/pages', ['draft_id' => $draft->id])
            ->assertOk()
            ->assertJsonPath('updated', true);

        $this->assertSame(1, $this->store->pages()->count(), 'editing must not create a second page');
        $this->assertStringContainsString('নতুন শিরোনাম', $this->get('http://organicfood.sellpata.com/mango')->getContent());
    }

    public function test_downgrade_requires_choosing_pages_and_preserves_content(): void
    {
        $this->givePlan('starter');
        $keep = $this->page('mango', 'আম');
        $drop = $this->page('lichu', 'লিচু');

        $free = Plan::where('slug', 'free')->firstOrFail();

        $this->actingAs(User::find($this->user->id))
            ->postJson("/api/plans/{$free->slug}/downgrade", [])
            ->assertStatus(422)
            ->assertJsonPath('code', 'choose_pages');

        $this->actingAs(User::find($this->user->id))
            ->postJson("/api/plans/{$free->slug}/downgrade", ['page_ids' => [$keep->id]])
            ->assertOk();

        $this->assertSame('published', $keep->fresh()->status);
        $this->assertSame('draft', $drop->fresh()->status);
        $this->assertNotEmpty($drop->fresh()->content, 'content must survive a downgrade');
        $this->assertSame('free', $this->store->fresh()->plan()->slug);
        $this->assertDatabaseHas('audit_logs', ['action' => 'plan.downgraded']);
    }

    public function test_csv_export_follows_the_plan(): void
    {
        $this->page('mango', 'আম');

        $this->actingAs(User::find($this->user->id))->get('/api/orders/export')->assertForbidden();

        $this->givePlan('starter');
        $response = $this->actingAs(User::find($this->user->id))->get('/api/orders/export');

        $response->assertOk();
        $this->assertStringContainsString('অর্ডার', $response->streamedContent());
    }

    public function test_subscription_invoice_downloads_as_a_pdf(): void
    {
        $this->givePlan('growth');
        $this->page('mango', 'আম');

        $subscription = $this->store->activeSubscription();
        $invoice = app(\App\Services\Platform\InvoiceService::class)
            ->createSubscriptionInvoice($subscription, 0, 'unpaid');

        $response = $this->actingAs(User::find($this->user->id))->get("/api/invoices/{$invoice->id}/download");

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertSame(Invoice::SUBSCRIPTION, $invoice->type);
    }
}
