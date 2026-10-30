@extends('admin.layout')
@section('title', 'SEO')
@section('content')
    <h1>SEO</h1>
    <p class="lede">সাইটের ডিফল্ট মেটা ট্যাগ, ব্লগের SEO এবং প্রাইভেসি/শর্তাবলির লেখা এখান থেকেই নিয়ন্ত্রণ করুন।</p>

    @if (session('status'))<p class="pill pill--on">{{ session('status') }}</p>@endif

    <form method="post" action="{{ route('admin.seo.update') }}">
        @csrf @method('put')
        <div class="grid">
            <div class="card">
                <h2>ওয়েবসাইট SEO</h2>
                <label for="site_title">ডিফল্ট শিরোনাম</label>
                <input id="site_title" name="seo.site.title" value="{{ old('seo.site.title', $values['seo.site.title']) }}">
                <label for="site_description">ডিফল্ট বর্ণনা</label>
                <textarea id="site_description" name="seo.site.description" rows="3">{{ old('seo.site.description', $values['seo.site.description']) }}</textarea>
                <label for="site_keywords">কীওয়ার্ড (কমা দিয়ে আলাদা)</label>
                <input id="site_keywords" name="seo.site.keywords" value="{{ old('seo.site.keywords', $values['seo.site.keywords']) }}">
                <label for="og_image">শেয়ার ছবির URL (OG image)</label>
                <input id="og_image" name="seo.og_image" value="{{ old('seo.og_image', $values['seo.og_image']) }}">
                <label for="robots">robots</label>
                <input id="robots" name="seo.robots" value="{{ old('seo.robots', $values['seo.robots'] ?: 'index,follow') }}">
                <label for="gv">Google Search Console verification</label>
                <input id="gv" name="seo.google_verification" value="{{ old('seo.google_verification', $values['seo.google_verification']) }}">
                <label for="ga">Analytics ID (GA4)</label>
                <input id="ga" name="seo.analytics_id" value="{{ old('seo.analytics_id', $values['seo.analytics_id']) }}">
            </div>

            <div class="card">
                <h2>ব্লগ SEO</h2>
                <label for="blog_title">ব্লগ পাতার শিরোনাম</label>
                <input id="blog_title" name="seo.blog.title" value="{{ old('seo.blog.title', $values['seo.blog.title']) }}">
                <label for="blog_description">ব্লগ পাতার বর্ণনা</label>
                <textarea id="blog_description" name="seo.blog.description" rows="3">{{ old('seo.blog.description', $values['seo.blog.description']) }}</textarea>

                <h2 style="margin-top:22px">প্রাইভেসি পলিসি</h2>
                <textarea name="legal.privacy.body" rows="10">{{ old('legal.privacy.body', $values['legal.privacy.body']) }}</textarea>
                <p class="muted">শেষ আপডেট: {{ $values['legal.privacy.updated'] ?: '—' }}</p>

                <h2 style="margin-top:22px">শর্তাবলি</h2>
                <textarea name="legal.terms.body" rows="10">{{ old('legal.terms.body', $values['legal.terms.body']) }}</textarea>
                <p class="muted">শেষ আপডেট: {{ $values['legal.terms.updated'] ?: '—' }}</p>

                <button class="btn" type="submit">সংরক্ষণ করুন</button>
            </div>
        </div>
    </form>
@endsection
