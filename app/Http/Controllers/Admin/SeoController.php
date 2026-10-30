<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public const KEYS = [
        'seo.site.title', 'seo.site.description', 'seo.site.keywords',
        'seo.og_image', 'seo.google_verification', 'seo.analytics_id',
        'seo.robots', 'seo.blog.title', 'seo.blog.description',
        'legal.privacy.body', 'legal.terms.body',
        'legal.privacy.updated', 'legal.terms.updated',
    ];

    public function index()
    {
        return view('admin.seo.index', [
            'values' => collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => Setting::get($key)])->all(),
        ]);
    }

    public function update(Request $request)
    {
        // Dotted keys are literal input names here, so they are read directly
        // instead of through validate(), which would treat the dot as nesting.
        $limits = [
            'seo.site.title' => 180,
            'seo.site.description' => 320,
            'seo.site.keywords' => 320,
            'seo.og_image' => 255,
            'seo.google_verification' => 120,
            'seo.analytics_id' => 60,
            'seo.robots' => 120,
            'seo.blog.title' => 180,
            'seo.blog.description' => 320,
        ];

        foreach ($limits as $key => $max) {
            $value = $request->input($key);
            Setting::updateOrCreate(['key' => $key], ['value' => is_string($value) ? mb_substr(trim($value), 0, $max) : '']);
        }

        foreach (['legal.privacy.body', 'legal.terms.body'] as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $request->input($key, '')]);
        }

        foreach (['privacy', 'terms'] as $doc) {
            Setting::updateOrCreate(
                ['key' => "legal.{$doc}.updated"],
                ['value' => now()->toDateString()]
            );
        }

        Cache::flush();

        return back()->with('status', 'SEO ও লিগ্যাল কনটেন্ট সংরক্ষণ হয়েছে।');
    }
}
