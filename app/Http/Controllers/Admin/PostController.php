<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $items = Post::latest('id')->get();
        $editing = $request->filled('edit') ? Post::find($request->integer('edit')) : null;

        return view('admin.posts.index', [
            'items' => $items,
            'editing' => $editing,
        ]);
    }

    public function store(Request $request)
    {
        Post::create($this->validated($request));

        return back()->with('status', 'পোস্ট যোগ হয়েছে।');
    }

    public function update(Request $request, Post $post)
    {
        $post->update($this->validated($request, $post));

        return redirect()->route('admin.posts.index')->with('status', 'পোস্ট আপডেট হয়েছে।');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('status', 'পোস্ট মুছে ফেলা হয়েছে।');
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', 'unique:posts,slug'.($post ? ','.$post->id : '')],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'body' => ['required', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
            'author' => ['nullable', 'string', 'max:120'],
        ], [], [
            'title' => 'শিরোনাম',
            'body' => 'বিষয়বস্তু',
            'slug' => 'স্লাগ',
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['slug'] = filled($data['slug'] ?? null) ? $data['slug'] : null;

        return $data;
    }
}
