<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuilderTemplate;

class PublicTemplateController extends Controller
{
    /** Read-only demo payload. Draft content is never served here. */
    public function show(string $slug)
    {
        $template = BuilderTemplate::where('slug', $slug)
            ->where('is_published', true)
            ->with('category')
            ->firstOrFail();

        return response()->json([
            'name' => $template->name,
            'slug' => $template->slug,
            'design' => $template->design,
            'category' => $template->category?->label,
            'content' => $template->content,
        ])->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
