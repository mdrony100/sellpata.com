<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuilderTemplate;
use App\Models\Category;
use App\Support\Landing\ThemeCatalog;
use Illuminate\Http\Request;

class LandingThemeController extends Controller
{
    public function index()
    {
        $editing = null;
        if ($id = request('edit')) {
            $editing = BuilderTemplate::whereIn('design', ThemeCatalog::DESIGNS)->find($id);
        }

        return view('admin.landing-themes.index', [
            'items' => BuilderTemplate::query()
                ->whereIn('design', ThemeCatalog::DESIGNS)
                ->with('category')
                ->orderBy('sort_order')
                ->get(),
            'categories' => Category::orderBy('sort_order')->get(),
            'editing' => $editing,
        ]);
    }

    public function update(Request $request, BuilderTemplate $landingTheme)
    {
        abort_unless(ThemeCatalog::isLanding($landingTheme->design), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'category_id' => ['required', 'exists:categories,id'],
            'theme_version' => ['required', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'suitable_for' => ['nullable', 'array'],
            'suitable_for.*' => ['string', 'max:40'],
            'content_json' => ['nullable', 'string'],
        ]);

        $payload = [
            'name' => $data['name'],
            'tagline' => $data['tagline'] ?? null,
            'category_id' => $data['category_id'],
            'theme_version' => $data['theme_version'],
            'sort_order' => $data['sort_order'] ?? 0,
            'suitable_for' => array_values($data['suitable_for'] ?? []),
            'is_published' => $request->boolean('is_published'),
        ];

        if (trim((string) ($data['content_json'] ?? '')) !== '') {
            $decoded = json_decode($data['content_json'], true);
            if (! is_array($decoded) || empty($decoded['hero']['headline']) || empty($decoded['product']['name'])) {
                return back()->withErrors(['content_json' => 'ডিফল্ট কনটেন্টে শিরোনাম ও পণ্যের নাম থাকতে হবে।'])->withInput();
            }
            $payload['content'] = ThemeCatalog::constrain($decoded);
        }

        $landingTheme->update($payload);

        return redirect()
            ->route('admin.landing-themes.index')
            ->with('status', 'থিমের ডিফল্ট আপডেট হয়েছে। ইতিমধ্যে প্রকাশিত পেজ বদলায়নি।');
    }
}
