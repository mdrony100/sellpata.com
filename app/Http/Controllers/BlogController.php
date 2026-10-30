<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Setting;

class BlogController extends Controller
{
    public function index()
    {
        return view('app', ['seo' => [
            'title' => Setting::get('seo.blog.title') ?: 'ব্লগ — SellPata',
            'description' => Setting::get('seo.blog.description') ?: 'ল্যান্ডিং পেজ, ফেসবুক বিজনেস আর অনলাইন বিক্রি নিয়ে সেলপাতার ব্লগ।',
            'canonical' => url('/blog'),
            'robots' => 'index,follow',
        ]]);
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();
        $post->increment('views');

        return view('app', ['seo' => [
            'title' => $post->metaTitle(),
            'description' => $post->metaDescription(),
            'canonical' => route('blog.show', $post->slug),
            'image' => $post->cover_image ? url($post->cover_image) : null,
            'type' => 'article',
            'robots' => 'index,follow',
            'jsonld' => [
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'description' => $post->metaDescription(),
                'datePublished' => optional($post->published_at)->toIso8601String(),
                'dateModified' => optional($post->updated_at)->toIso8601String(),
                'author' => ['@type' => 'Organization', 'name' => $post->author ?: Setting::get('brand.name')],
                'mainEntityOfPage' => route('blog.show', $post->slug),
            ],
        ]]);
    }

    public function apiIndex()
    {
        $posts = Post::published()->latest('published_at')->paginate(9);

        return response()->json([
            'items' => collect($posts->items())->map->toCard(),
            'has_more' => $posts->hasMorePages(),
            'page' => $posts->currentPage(),
        ]);
    }

    public function apiShow(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return response()->json([
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => (string) $post->body,
            'cover_image' => $post->cover_image,
            'author' => $post->author,
            'published_at' => optional($post->published_at)->toDateString(),
        ]);
    }

    public function sitemap()
    {
        $urls = collect([
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => route('blog.index'), 'priority' => '0.8'],
            ['loc' => url('/clients'), 'priority' => '0.6'],
            ['loc' => url('/contact'), 'priority' => '0.6'],
            ['loc' => url('/privacy-policy'), 'priority' => '0.3'],
            ['loc' => url('/terms'), 'priority' => '0.3'],
        ])->merge(
            Post::published()->get()->map(fn (Post $post) => [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => optional($post->updated_at)->toAtomString(),
                'priority' => '0.7',
            ])
        );

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
