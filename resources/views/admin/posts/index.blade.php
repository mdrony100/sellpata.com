@extends('admin.layout')
@section('title', 'ব্লগ')
@section('content')
    <h1>ব্লগ</h1>
    <p class="lede">পোস্ট লিখুন, ড্রাফটে রাখুন বা প্রকাশ করুন। প্রতিটি পোস্টের নিজস্ব SEO শিরোনাম ও বর্ণনা দেওয়া যায়।</p>

    @if (session('status'))<p class="pill pill--on">{{ session('status') }}</p>@endif

    <div class="grid">
        <div class="card">
            <h2>{{ $editing ? 'পোস্ট সম্পাদনা' : 'নতুন পোস্ট' }}</h2>
            <form method="post" action="{{ $editing ? route('admin.posts.update', $editing) : route('admin.posts.store') }}">
                @csrf
                @if ($editing) @method('put') @endif

                <label for="title">শিরোনাম</label>
                <input id="title" name="title" value="{{ old('title', $editing->title ?? '') }}" required>

                <label for="slug">স্লাগ (খালি রাখলে নিজে তৈরি হবে)</label>
                <input id="slug" name="slug" value="{{ old('slug', $editing->slug ?? '') }}" placeholder="facebook-business-landing-page">

                <label for="excerpt">সংক্ষিপ্ত পরিচিতি</label>
                <textarea id="excerpt" name="excerpt" rows="2">{{ old('excerpt', $editing->excerpt ?? '') }}</textarea>

                <label for="body">বিষয়বস্তু (HTML ব্যবহার করা যাবে)</label>
                <textarea id="body" name="body" rows="10" required>{{ old('body', $editing->body ?? '') }}</textarea>

                <label for="cover_image">কভার ছবির URL</label>
                <input id="cover_image" name="cover_image" value="{{ old('cover_image', $editing->cover_image ?? '') }}">

                <label for="author">লেখক</label>
                <input id="author" name="author" value="{{ old('author', $editing->author ?? '') }}">

                <h2 style="margin-top:22px">SEO</h2>
                <label for="meta_title">SEO শিরোনাম</label>
                <input id="meta_title" name="meta_title" value="{{ old('meta_title', $editing->meta_title ?? '') }}" maxlength="180">
                <label for="meta_description">SEO বর্ণনা (সর্বোচ্চ ৩২০ অক্ষর)</label>
                <textarea id="meta_description" name="meta_description" rows="2" maxlength="320">{{ old('meta_description', $editing->meta_description ?? '') }}</textarea>
                <label for="focus_keyword">ফোকাস কীওয়ার্ড</label>
                <input id="focus_keyword" name="focus_keyword" value="{{ old('focus_keyword', $editing->focus_keyword ?? '') }}">

                <label style="display:flex;gap:8px;align-items:center;margin-top:14px">
                    <input type="checkbox" name="is_published" value="1" style="width:auto" @checked(old('is_published', $editing->is_published ?? false))>
                    প্রকাশিত
                </label>

                <button class="btn" type="submit">{{ $editing ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}</button>
                @if ($editing)<a class="btn btn--ghost" href="{{ route('admin.posts.index') }}">বাতিল</a>@endif
            </form>
        </div>

        <div class="card">
            <h2>সব পোস্ট</h2>
            <table>
                <thead><tr><th>শিরোনাম</th><th>স্লাগ</th><th>অবস্থা</th><th></th></tr></thead>
                <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->title }}</td>
                        <td class="muted">/blog/{{ $item->slug }}</td>
                        <td><span class="pill {{ $item->is_published ? 'pill--on' : '' }}">{{ $item->is_published ? 'প্রকাশিত' : 'ড্রাফট' }}</span></td>
                        <td style="white-space:nowrap">
                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.posts.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                            <form method="post" action="{{ route('admin.posts.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
                                @csrf @method('delete')
                                <button class="btn btn--danger" type="submit">মুছুন</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">এখনো কোনো পোস্ট নেই।</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
