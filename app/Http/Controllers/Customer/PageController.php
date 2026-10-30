<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Draft;
use App\Models\Page;
use App\Services\Platform\Entitlements;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageController extends Controller
{
    private function store(Request $request)
    {
        $store = $request->user()->store;
        abort_if($store === null, 409, 'আগে আপনার স্টোরের ঠিকানা বেছে নিন।');

        return $store;
    }

    private function owned(Request $request, Page $page): Page
    {
        abort_unless($page->store_id === $this->store($request)->id, 404);

        return $page;
    }

    public function index(Request $request)
    {
        $store = $this->store($request);
        $pages = $store->pages()->latest()->get();

        return response()->json([
            'store' => ['username' => $store->username, 'url' => $store->publicUrl()],
            'limit' => Entitlements::for($store)->maxPages(),
            'pages' => $pages->map(fn (Page $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'status' => $p->status,
                'is_primary' => $p->is_primary,
                'url' => $p->status === 'published' ? $store->publicUrl($p) : null,
                'updated_at' => $p->updated_at?->toIso8601String(),
                'template' => $p->template?->name,
                'thumbnail' => $p->template?->thumbnail,
            ]),
        ]);
    }

    /** Turn a claimed draft (from the builder) into a landing page. */
    public function fromDraft(Request $request)
    {
        $store = $this->store($request);
        $entitlements = Entitlements::for($store);

        $data = $request->validate(['draft_id' => ['required', 'integer']]);
        $draft = Draft::findOrFail($data['draft_id']);
        abort_unless($draft->user_id === $request->user()->id, 404);

        if ($existing = $store->pages()->where('draft_id', $draft->id)->first()) {
            return response()->json(['ok' => true, 'page_id' => $existing->id, 'duplicate' => true]);
        }

        if (! $entitlements->canCreatePage()) {
            return response()->json([
                'message' => 'আপনার প্ল্যানের ল্যান্ডিং পেজের সীমা শেষ হয়েছে। নতুন পেজ বানাতে প্ল্যান আপগ্রেড করুন বা একটি পেজ মুছে দিন। আপনার কাজ হারায়নি—ড্রাফটটি সংরক্ষিত আছে।',
                'code' => 'page_limit',
                'draft_id' => $draft->id,
            ], 422);
        }

        $content = $draft->content;
        $template = $draft->template;
        $title = $content['product']['name'] ?? $content['brand']['name'] ?? $template->name;

        $page = DB::transaction(function () use ($store, $draft, $template, $content, $title) {
            return Page::create([
                'store_id' => $store->id,
                'draft_id' => $draft->id,
                'template_id' => $template->id,
                'title' => Str::limit($title, 90, ''),
                'slug' => $this->uniqueSlug($store, $template->slug),
                'status' => 'draft',
                'content' => $content,
                'revision' => 1,
            ]);
        });

        AuditLog::record('page.created', $page, ['draft_id' => $draft->id], $request->user()->id);

        return response()->json(['ok' => true, 'page_id' => $page->id, 'slug' => $page->slug], 201);
    }

    public function update(Request $request, Page $page)
    {
        $page = $this->owned($request, $page);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:120'],
            'slug' => ['sometimes', 'string', 'max:60'],
            'content' => ['sometimes', 'array'],
            'revision' => ['sometimes', 'integer'],
        ]);

        if (isset($data['content'])) {
            // Working copy only: the live page keeps its published snapshot.
            if (isset($data['revision']) && $data['revision'] !== $page->revision) {
                return response()->json(['conflict' => true, 'page' => ['revision' => $page->revision, 'content' => $page->content]], 409);
            }
            $page->content = $data['content'];
            $page->revision++;
        }

        if (isset($data['title'])) {
            $page->title = $data['title'];
        }

        if (isset($data['slug']) && $data['slug'] !== $page->slug) {
            $slug = Str::slug($data['slug'], '-');
            if ($slug === '' || in_array($slug, config('platform.reserved_slugs'), true)) {
                return response()->json(['message' => 'এই ঠিকানাটি ব্যবহার করা যাবে না।', 'errors' => ['slug' => ['অন্য ঠিকানা বেছে নিন।']]], 422);
            }
            if ($page->store->pages()->where('slug', $slug)->whereKeyNot($page->id)->exists()) {
                return response()->json(['message' => 'এই ঠিকানাটি ইতিমধ্যে ব্যবহৃত।', 'errors' => ['slug' => ['অন্য ঠিকানা বেছে নিন।']]], 422);
            }
            if ($page->status === 'published') {
                AuditLog::record('page.slug_changed', $page, ['from' => $page->slug, 'to' => $slug], $request->user()->id);
            }
            $page->slug = $slug;
        }

        $page->save();

        return response()->json(['ok' => true, 'page' => ['id' => $page->id, 'title' => $page->title, 'slug' => $page->slug, 'revision' => $page->revision]]);
    }

    public function publish(Request $request, Page $page)
    {
        $page = $this->owned($request, $page);
        $store = $page->store;

        // Publishing an existing page never consumes a new slot; only creation does.
        $published = $store->pages()->where('status', 'published')->count();
        if ($page->status !== 'published' && $published >= Entitlements::for($store)->maxPages()) {
            return response()->json(['message' => 'আপনার প্ল্যানের পেজের সীমা শেষ।', 'code' => 'page_limit'], 422);
        }

        DB::transaction(function () use ($page, $store, $request) {
            $page->forceFill([
                'status' => 'published',
                'published_content' => $page->content,
                'published_at' => now(),
            ])->save();

            if (! $store->pages()->where('is_primary', true)->where('status', 'published')->exists()) {
                $page->forceFill(['is_primary' => true])->save();
            }

            AuditLog::record('page.published', $page, [], $request->user()->id);
        });

        return response()->json([
            'ok' => true,
            'url' => $store->publicUrl($page->fresh()),
            'is_primary' => $page->fresh()->is_primary,
        ]);
    }

    public function unpublish(Request $request, Page $page)
    {
        $page = $this->owned($request, $page);

        if ($page->is_primary && $page->store->pages()->where('status', 'published')->count() > 1) {
            return response()->json(['message' => 'মূল পেজ বন্ধ করার আগে অন্য একটি পেজকে মূল পেজ হিসেবে বেছে নিন।'], 422);
        }

        $page->forceFill([
            'status' => 'draft',
            'is_primary' => false,
        ])->save();

        AuditLog::record('page.unpublished', $page, [], $request->user()->id);

        return response()->json(['ok' => true]);
    }

    public function primary(Request $request, Page $page)
    {
        $page = $this->owned($request, $page);

        if ($page->status !== 'published') {
            return response()->json(['message' => 'মূল পেজ করতে আগে পেজটি প্রকাশ করুন।'], 422);
        }

        DB::transaction(function () use ($page, $request) {
            $page->store->pages()->where('is_primary', true)->update(['is_primary' => false]);
            $page->forceFill(['is_primary' => true])->save();
            AuditLog::record('page.primary', $page, [], $request->user()->id);
        });

        return response()->json(['ok' => true, 'url' => $page->store->publicUrl($page)]);
    }

    public function duplicate(Request $request, Page $page)
    {
        $page = $this->owned($request, $page);
        $store = $page->store;

        if (! Entitlements::for($store)->canCreatePage()) {
            return response()->json(['message' => 'এই প্ল্যানে আরেকটি পেজ তৈরির সুযোগ নেই।', 'code' => 'page_limit'], 422);
        }

        $copy = Page::create([
            'store_id' => $store->id,
            'template_id' => $page->template_id,
            'title' => Str::limit($page->title.' (কপি)', 110, ''),
            'slug' => $this->uniqueSlug($store, $page->slug.'-copy'),
            'status' => 'draft',
            'content' => $page->content,
            'revision' => 1,
        ]);

        return response()->json(['ok' => true, 'page_id' => $copy->id], 201);
    }

    public function destroy(Request $request, Page $page)
    {
        $page = $this->owned($request, $page);

        if ($page->is_primary && $page->store->pages()->where('status', 'published')->count() > 1) {
            return response()->json(['message' => 'মূল পেজ মুছতে আগে অন্য পেজকে মূল করুন।'], 422);
        }

        AuditLog::record('page.deleted', $page, ['title' => $page->title], $request->user()->id);
        $page->delete(); // orders and invoices keep their own snapshots

        return response()->json(['ok' => true]);
    }

    private function uniqueSlug($store, string $base): string
    {
        $base = Str::slug($base, '-') ?: 'page';
        $slug = $base;
        $i = 2;

        while ($store->pages()->where('slug', $slug)->exists() || in_array($slug, config('platform.reserved_slugs'), true)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
