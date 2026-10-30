<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuilderTemplate;
use App\Models\Category;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $categorySlug = $request->query('category');

        $groups = Category::whereNull('parent_id')
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')])
            ->get();

        $categories = $groups->map(fn (Category $group) => [
            'slug' => $group->slug,
            'label' => $group->label,
            'keywords' => $group->keywords,
            'children' => $group->children->map(fn (Category $child) => [
                'slug' => $child->slug,
                'label' => $child->label,
                'keywords' => $child->keywords,
            ])->all(),
        ]);

        $activeCategory = null;

        if ($categorySlug) {
            $activeCategory = Category::where('slug', $categorySlug)->with('parent')->first();
        }

        $templatesQuery = BuilderTemplate::query()
            ->where('is_published', true)
            ->with('category.parent')
            ->orderBy('sort_order');

        if ($activeCategory) {
            $ids = $activeCategory->children()->pluck('id')->push($activeCategory->id);
            $slug = $activeCategory->slug;
            $templatesQuery->where(function ($q) use ($ids, $slug) {
                $q->whereIn('category_id', $ids)
                    ->orWhereJsonContains('suitable_for', $slug);
            });
        }

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $matchingCategoryIds = Category::query()
                ->where(function ($q) use ($needle) {
                    $q->whereRaw('lower(label) like ?', ["%{$needle}%"])
                        ->orWhereRaw('lower(slug) like ?', ["%{$needle}%"])
                        ->orWhereRaw('lower(keywords) like ?', ["%{$needle}%"]);
                })
                ->pluck('id');

            $matchingCategoryIds = $matchingCategoryIds
                ->merge(Category::whereIn('parent_id', $matchingCategoryIds)->pluck('id'))
                ->unique();

            $templatesQuery->where(function ($q) use ($needle, $matchingCategoryIds) {
                $q->whereIn('category_id', $matchingCategoryIds)
                    ->orWhereRaw('lower(name) like ?', ["%{$needle}%"])
                    ->orWhereRaw('lower(tagline) like ?', ["%{$needle}%"]);
            });
        }

        $templates = $templatesQuery->get()->map(fn (BuilderTemplate $template) => [
            'id' => $template->id,
            'name' => $template->name,
            'slug' => $template->slug,
            'design' => $template->design,
            'tagline' => $template->tagline,
            'thumbnail' => $template->thumbnail,
            'preview' => $template->preview,
            'theme_version' => $template->theme_version,
            'suitable_for' => $template->suitable_for ?? [],
            'category' => $template->category?->label,
            'category_slug' => $template->category?->slug,
            'group' => $template->category?->parent?->label ?? $template->category?->label,
        ]);

        return response()->json([
            'categories' => $categories,
            'templates' => $templates,
            'active_category' => $activeCategory ? [
                'slug' => $activeCategory->slug,
                'label' => $activeCategory->label,
            ] : null,
            'query' => $query,
        ]);
    }
}
