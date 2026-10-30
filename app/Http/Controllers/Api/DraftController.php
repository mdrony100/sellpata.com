<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuilderTemplate;
use App\Models\Draft;
use App\Models\DraftMedia;
use App\Support\Landing\ThemeCatalog;
use App\Support\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class DraftController extends Controller
{
    /** Content keys a draft may contain. Anything else is dropped server-side. */
    private const ALLOWED = [
        'brand', 'hero', 'product', 'cta', 'delivery', 'contact', 'features', 'faq', 'sections',
        'benefits', 'gallery', 'reviews', 'variants', 'bundles', 'policies', 'details',
        'section_order', 'selection', 'headings', 'offer', 'tracking',
    ];

    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id' => ['required', 'integer', 'exists:builder_templates,id'],
        ]);

        $template = BuilderTemplate::where('is_published', true)->findOrFail($data['template_id']);
        $identity = VisitorIdentity::ensure($request);

        // Double clicks and quick retries reuse the draft instead of creating copies.
        $existing = Draft::where('anonymous_identity_id', $identity->id)
            ->where('builder_template_id', $template->id)
            ->whereNull('user_id')
            ->where('created_at', '>=', now()->subSeconds(20))
            ->latest('id')
            ->first();

        if ($existing) {
            return response()->json(['draft' => $this->payload($existing), 'reused' => true], 200);
        }

        $draft = Draft::create([
            'builder_template_id' => $template->id,
            'anonymous_identity_id' => $identity->id,
            'title' => $template->name,
            'content' => $template->content,
            'revision' => 1,
            'status' => 'draft',
            'last_saved_at' => now(),
        ]);

        return response()->json(['draft' => $this->payload($draft), 'reused' => false], 201);
    }

    public function show(Request $request, string $publicId)
    {
        $draft = $this->authorize($request, $publicId);
        $draft->loadMissing('template.category');

        return response()->json([
            // While SMS is unwired the client hides the code step entirely.
            'skip_otp' => (bool) config('otp.skip_verification'),
            'draft' => $this->payload($draft),
            'template' => [
                'id' => $draft->template->id,
                'name' => $draft->template->name,
                'slug' => $draft->template->slug,
                'design' => $draft->template->design,
                'schema' => $draft->template->schema,
                'category' => $draft->template->category?->label,
            ],
            'media' => $this->mediaPayload($draft),
            'themes' => $this->themeChoices(),
        ]);
    }

    /** Switch presentation. Compatible copy stays; hero mode and section order follow the new theme. */
    public function switchTheme(Request $request, string $publicId)
    {
        $draft = $this->authorize($request, $publicId);

        $data = $request->validate([
            'template_id' => ['required', 'integer', 'exists:builder_templates,id'],
            'confirm' => ['required', 'accepted'],
        ]);

        $next = BuilderTemplate::where('is_published', true)->findOrFail($data['template_id']);
        abort_unless(ThemeCatalog::isLanding($next->design), 422, 'এই ডিজাইনে বদলানো যায় না।');

        $merged = ThemeCatalog::mergeContent($draft->content ?? [], $next->content ?? []);

        $draft->forceFill([
            'builder_template_id' => $next->id,
            'content' => $this->sanitize($merged),
            'revision' => $draft->revision + 1,
            'title' => $next->name,
            'last_saved_at' => now(),
        ])->save();

        $draft->unsetRelation('template');

        return response()->json([
            'saved' => true,
            'draft' => $this->payload($draft->fresh()),
            'template' => [
                'id' => $next->id,
                'name' => $next->name,
                'slug' => $next->slug,
                'design' => $next->design,
                'schema' => $next->schema,
                'category' => $next->category?->label,
            ],
            'themes' => $this->themeChoices(),
        ]);
    }

    public function update(Request $request, string $publicId)
    {
        $draft = $this->authorize($request, $publicId);

        $data = $request->validate([
            'content' => ['required', 'array'],
            'revision' => ['required', 'integer', 'min:1'],
        ]);

        if ($data['revision'] !== $draft->revision) {
            return response()->json([
                'conflict' => true,
                'message' => 'অন্য একটি ট্যাব থেকে পরিবর্তন সংরক্ষিত হয়েছে।',
                'draft' => $this->payload($draft->fresh()),
            ], 409);
        }

        $content = $this->sanitize($data['content']);

        if (strlen(json_encode($content, JSON_UNESCAPED_UNICODE)) > 120000) {
            throw ValidationException::withMessages(['content' => 'কনটেন্টের আকার সীমার বেশি।']);
        }

        $draft->forceFill([
            'content' => $content,
            'revision' => $draft->revision + 1,
            'last_saved_at' => now(),
        ])->save();

        return response()->json([
            'saved' => true,
            'revision' => $draft->revision,
            'saved_at' => $draft->last_saved_at->toIso8601String(),
        ]);
    }

    public function authorize(Request $request, string $publicId): Draft
    {
        $draft = Draft::where('public_id', $publicId)->first();

        // Unknown ids and drafts owned by someone else look identical on purpose.
        abort_if($draft === null, 404);

        $userId = $request->user()?->id;
        $identity = VisitorIdentity::current($request);

        $allowed = ($draft->user_id !== null && $draft->user_id === $userId)
            || ($draft->user_id === null && $draft->anonymous_identity_id !== null
                && $identity !== null && $draft->anonymous_identity_id === $identity->id);

        abort_unless($allowed, 404);

        return $draft;
    }

    private function sanitize(array $content): array
    {
        $content = array_intersect_key($content, array_flip(self::ALLOWED));

        return ThemeCatalog::constrain($content);
    }

    private function themeChoices(): array
    {
        return BuilderTemplate::query()
            ->whereIn('design', ThemeCatalog::DESIGNS)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'design', 'tagline'])
            ->map(fn (BuilderTemplate $theme) => [
                'id' => $theme->id,
                'name' => $theme->name,
                'slug' => $theme->slug,
                'design' => $theme->design,
                'tagline' => $theme->tagline,
            ])
            ->all();
    }

    private function payload(Draft $draft): array
    {
        return [
            // Numeric id is what the page-creation endpoint takes.
            'id' => $draft->id,
            'public_id' => $draft->public_id,
            'title' => $draft->title,
            'content' => $draft->content,
            'revision' => $draft->revision,
            'status' => $draft->status,
            'claimed' => $draft->user_id !== null,
            'last_saved_at' => $draft->last_saved_at?->toIso8601String(),
        ];
    }

    private function mediaPayload(Draft $draft): array
    {
        return $draft->media()->get()->mapWithKeys(fn (DraftMedia $media) => [
            $media->field => URL::temporarySignedRoute('draft.media.show', now()->addMinutes(30), ['media' => $media->id]),
        ])->all();
    }
}
