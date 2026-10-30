@extends('admin.layout')
@section('title', 'ব্লগ')

@section('content')
<style>
    .ba{--ink:#16423c;--cream:#f9f7f0;--line:#e4e0d3;--muted:#6d7a75;max-width:1180px}
    .ba__top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}
    .ba__top h1{margin:0 0 6px;font-size:1.6rem;color:var(--ink)}
    .ba__top p{margin:0;color:var(--muted);font-size:.95rem;max-width:60ch;line-height:1.6}
    .ba__layout{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(280px,1fr);gap:18px;align-items:start}
    @media (max-width:960px){.ba__layout{grid-template-columns:1fr}}
    .ba__card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:20px 20px 22px;margin-bottom:18px}
    .ba__card>h2{margin:0 0 4px;font-size:1rem;color:var(--ink);letter-spacing:.2px}
    .ba__card>h2+.ba__sub{margin:0 0 16px}
    .ba__sub{margin:0;color:var(--muted);font-size:.85rem}
    .ba__field{display:block;margin-bottom:16px}
    .ba__field:last-of-type{margin-bottom:0}
    .ba__label{display:flex;justify-content:space-between;gap:10px;font-size:.88rem;font-weight:600;color:#2b3a35;margin-bottom:6px}
    .ba__hint{font-weight:400;color:var(--muted);font-size:.78rem}
    .ba input[type=text],.ba input[type=url],.ba textarea{
        width:100%;padding:11px 13px;border:1px solid var(--line);border-radius:10px;
        background:#fcfcfa;font:inherit;font-size:.95rem;color:#1d2b27;transition:border-color .15s,box-shadow .15s,background .15s}
    .ba input[type=text]:focus,.ba input[type=url]:focus,.ba textarea:focus{
        outline:none;background:#fff;border-color:var(--ink);box-shadow:0 0 0 3px rgba(22,66,60,.1)}
    .ba textarea{resize:vertical;line-height:1.65}
    .ba__title-input{font-size:1.15rem!important;font-weight:600;padding:14px!important}
    .ba__body-textarea{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.88rem!important;min-height:340px}
    .ba__row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    @media (max-width:640px){.ba__row{grid-template-columns:1fr}}
    .ba__switch{display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid var(--line);border-radius:10px;background:#fcfcfa;cursor:pointer}
    .ba__switch input{width:auto;margin:0;accent-color:var(--ink);transform:scale(1.15)}
    .ba__switch span{font-size:.9rem;color:#2b3a35;font-weight:600}
    .ba__switch small{display:block;font-weight:400;color:var(--muted);font-size:.78rem;margin-top:2px}
    .ba__preview{margin-top:10px;border:1px dashed var(--line);border-radius:10px;overflow:hidden;background:var(--cream);display:grid;place-items:center;min-height:120px}
    .ba__preview img{width:100%;max-height:200px;object-fit:cover;display:block}
    .ba__preview span{color:var(--muted);font-size:.82rem;padding:26px 12px;text-align:center}
    .ba__actions{position:sticky;bottom:0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;
        padding:14px 20px;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 -2px 12px rgba(22,66,60,.05)}
    .ba__actions .btn{min-width:150px;justify-content:center}
    .ba__count{font-size:.78rem;color:var(--muted);margin-left:auto;font-variant-numeric:tabular-nums}
    .ba__count.is-over{color:#b3261e;font-weight:600}
    .ba__list{margin-top:22px}
    .ba__list-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:10px}
    .ba__list-head h2{margin:0;font-size:1.05rem;color:var(--ink)}
    .ba__table-wrap{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden}
    .ba table{width:100%;border-collapse:collapse;margin:0}
    .ba thead th{background:var(--cream);color:var(--muted);font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;
        text-align:left;padding:11px 14px;border-bottom:1px solid var(--line);font-weight:600}
    .ba tbody td{padding:13px 14px;border-bottom:1px solid #f1eee4;vertical-align:middle;font-size:.92rem}
    .ba tbody tr:last-child td{border-bottom:0}
    .ba tbody tr:hover{background:#fcfcf8}
    .ba__post-title{font-weight:600;color:var(--ink);text-decoration:none;display:block}
    .ba__post-title:hover{text-decoration:underline}
    .ba__slug{color:var(--muted);font-size:.8rem}
    .ba__badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:.76rem;font-weight:600}
    .ba__badge--on{background:#e7f2ed;color:#14624f}
    .ba__badge--off{background:#f2f0e8;color:#7a7565}
    .ba__badge i{width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block}
    .ba__row-actions{display:flex;gap:8px;align-items:center;justify-content:flex-end}
    .ba__row-actions form{margin:0;display:inline}
    .ba__empty{padding:34px 16px;text-align:center;color:var(--muted);font-size:.9rem}
    .ba__flash{display:flex;align-items:center;gap:8px;background:#e7f2ed;color:#14624f;border:1px solid #cfe6dc;
        border-radius:10px;padding:11px 14px;font-size:.9rem;margin-bottom:16px}
</style>

<div class="ba">
    <div class="ba__top">
        <div>
            <h1>ব্লগ</h1>
            <p>পোস্ট লিখুন, ড্রাফটে রাখুন বা প্রকাশ করুন। প্রতিটি পোস্টের নিজস্ব SEO শিরোনাম ও বর্ণনা দেওয়া যায়।</p>
        </div>
        <a class="btn" href="{{ route('admin.posts.index') }}">＋ নতুন পোস্ট</a>
    </div>

    @if (session('status'))
        <div class="ba__flash">✓ {{ session('status') }}</div>
    @endif

    <div class="ba__layout">
        <form id="post-form" method="post" action="{{ $editing ? route('admin.posts.update', $editing) : route('admin.posts.store') }}">
            @csrf
            @if ($editing) @method('put') @endif

            <div class="ba__card">
                <h2>মূল তথ্য</h2>
                <p class="ba__sub">শিরোনাম ও স্লাগ দিয়েই পোস্টের ঠিকানা তৈরি হয়।</p>

                <label class="ba__field" for="title">
                    <span class="ba__label">শিরোনাম <span class="ba__hint">যা পাঠকেরা দেখবেন</span></span>
                    <input id="title" class="ba__title-input" name="title" value="{{ old('title', $editing->title ?? '') }}" placeholder="যেমন: ফেসবুক ব্যবসার জন্য ল্যান্ডিং পেজ কেন দরকার" required>
                </label>

                <label class="ba__field" for="slug">
                    <span class="ba__label">স্লাগ <span class="ba__hint">খালি রাখলে নিজে তৈরি হবে</span></span>
                    <input id="slug" name="slug" value="{{ old('slug', $editing->slug ?? '') }}" placeholder="facebook-business-landing-page">
                </label>

                <label class="ba__field" for="excerpt">
                    <span class="ba__label">সংক্ষিপ্ত পরিচিতি <span class="ba__hint">ব্লগ তালিকায় দেখা যাবে</span></span>
                    <textarea id="excerpt" name="excerpt" rows="2" placeholder="দুই লাইনে পোস্টটি কী নিয়ে, তা বলে দিন।">{{ old('excerpt', $editing->excerpt ?? '') }}</textarea>
                </label>

                <label class="ba__field" for="body">
                    <span class="ba__label">বিষয়বস্তু <span class="ba__hint">HTML ব্যবহার করা যাবে</span></span>
                    <textarea id="body" name="body" class="ba__body-textarea" rows="16" placeholder="<p>পোস্টের মূল লেখা এখানে...</p>" required>{{ old('body', $editing->body ?? '') }}</textarea>
                </label>
            </div>

            <div class="ba__actions">
                <button class="btn" type="submit">{{ $editing ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}</button>
                @if ($editing)
                    <a class="btn btn--ghost" href="{{ route('admin.posts.index') }}">বাতিল</a>
                @else
                    <a class="btn btn--ghost" href="{{ route('admin.posts.index') }}">রিসেট</a>
                @endif
                <span class="ba__count">বিষয়বস্তু: <b data-body-count>0</b> অক্ষর</span>
            </div>
        </form>

        <div>
            <div class="ba__card">
                <h2>প্রকাশ</h2>
                <p class="ba__sub">ড্রাফট অবস্থায় পোস্ট সাইটে দেখা যায় না।</p>
                <label class="ba__switch" for="is_published">
                    <input type="checkbox" id="is_published" name="is_published" value="1" form="post-form" @checked(old('is_published', $editing->is_published ?? false))>
                    <span>প্রকাশিত
                        <small>টিক দিলে সাথে সাথে ব্লগে দেখা যাবে</small>
                    </span>
                </label>
            </div>

            <div class="ba__card">
                <h2>কভার ও লেখক</h2>
                <p class="ba__sub">কভার ছবি না দিলে শিরোনামসহ সাধারণ কার্ড দেখাবে।</p>

                <label class="ba__field" for="cover_image">
                    <span class="ba__label">কভার ছবির URL</span>
                    <input id="cover_image" name="cover_image" type="url" value="{{ old('cover_image', $editing->cover_image ?? '') }}" placeholder="https://…/cover.jpg" data-cover-input>
                </label>
                <div class="ba__preview" data-cover-preview>
                    <span>ছবির লিংক দিলে এখানে ঝলক দেখা যাবে</span>
                </div>

                <label class="ba__field" for="author" style="margin-top:16px">
                    <span class="ba__label">লেখক</span>
                    <input id="author" name="author" value="{{ old('author', $editing->author ?? '') }}" placeholder="SellPata টিম">
                </label>
            </div>

            <div class="ba__card">
                <h2>SEO</h2>
                <p class="ba__sub">গুগলে যেভাবে দেখাবে, সেটাই এখানে ঠিক করুন।</p>

                <label class="ba__field" for="meta_title">
                    <span class="ba__label">SEO শিরোনাম <span class="ba__hint">৫০–৬০ অক্ষর ভালো</span></span>
                    <input id="meta_title" name="meta_title" value="{{ old('meta_title', $editing->meta_title ?? '') }}" maxlength="180" placeholder="খালি রাখলে পোস্টের শিরোনাম ব্যবহার হবে">
                </label>

                <label class="ba__field" for="meta_description">
                    <span class="ba__label">SEO বর্ণনা <span class="ba__hint">সর্বোচ্চ ৩২০ অক্ষর</span></span>
                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="320" placeholder="১৪০–১৬০ অক্ষরে সারাংশ লিখুন।" data-meta>{{ old('meta_description', $editing->meta_description ?? '') }}</textarea>
                </label>
                <p class="ba__count" data-meta-count style="margin:-8px 0 16px">0/320</p>

                <label class="ba__field" for="focus_keyword">
                    <span class="ba__label">ফোকাস কীওয়ার্ড</span>
                    <input id="focus_keyword" name="focus_keyword" value="{{ old('focus_keyword', $editing->focus_keyword ?? '') }}" placeholder="ল্যান্ডিং পেজ">
                </label>
            </div>
        </div>
    </div>

    <div class="ba__list">
        <div class="ba__list-head">
            <h2>সব পোস্ট</h2>
            <span class="ba__sub">{{ $items->count() }} টি পোস্ট</span>
        </div>
        <div class="ba__table-wrap">
            <table>
                <thead>
                    <tr><th>শিরোনাম</th><th>অবস্থা</th><th>SEO</th><th style="text-align:right">অ্যাকশন</th></tr>
                </thead>
                <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <a class="ba__post-title" href="{{ route('admin.posts.index', ['edit' => $item->id]) }}">{{ $item->title }}</a>
                            <span class="ba__slug">/blog/{{ $item->slug }}</span>
                        </td>
                        <td>
                            <span class="ba__badge {{ $item->is_published ? 'ba__badge--on' : 'ba__badge--off' }}">
                                <i></i>{{ $item->is_published ? 'প্রকাশিত' : 'ড্রাফট' }}
                            </span>
                        </td>
                        <td>
                            @if ($item->meta_title || $item->meta_description)
                                <span class="ba__badge ba__badge--on">ঠিক আছে</span>
                            @else
                                <span class="ba__badge ba__badge--off">দেওয়া হয়নি</span>
                            @endif
                        </td>
                        <td>
                            <div class="ba__row-actions">
                                @if ($item->is_published)
                                    <a class="btn btn--ghost btn--sm" href="/blog/{{ $item->slug }}" target="_blank" rel="noopener">দেখুন</a>
                                @endif
                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.posts.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                                <form method="post" action="{{ route('admin.posts.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
                                    @csrf @method('delete')
                                    <button class="btn btn--danger btn--sm" type="submit">মুছুন</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ba__empty">এখনো কোনো পোস্ট নেই। ওপরের ফর্ম দিয়ে প্রথম পোস্ট লিখুন।</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(() => {
    const body = document.getElementById('body');
    const bodyCount = document.querySelector('[data-body-count]');
    const syncBody = () => { if (body && bodyCount) bodyCount.textContent = body.value.length; };
    body?.addEventListener('input', syncBody);
    syncBody();

    const meta = document.querySelector('[data-meta]');
    const metaCount = document.querySelector('[data-meta-count]');
    const syncMeta = () => {
        if (!meta || !metaCount) return;
        const n = meta.value.length;
        metaCount.textContent = n + '/320';
        metaCount.classList.toggle('is-over', n > 320);
    };
    meta?.addEventListener('input', syncMeta);
    syncMeta();

    const coverInput = document.querySelector('[data-cover-input]');
    const coverBox = document.querySelector('[data-cover-preview]');
    const syncCover = () => {
        if (!coverInput || !coverBox) return;
        const url = coverInput.value.trim();
        coverBox.innerHTML = '';
        if (!url) {
            coverBox.innerHTML = '<span>ছবির লিংক দিলে এখানে ঝলক দেখা যাবে</span>';
            return;
        }
        const img = new Image();
        img.src = url;
        img.alt = 'কভার ছবির ঝলক';
        img.onerror = () => { coverBox.innerHTML = '<span>ছবিটি লোড হয়নি, লিংকটি দেখে নিন</span>'; };
        coverBox.appendChild(img);
    };
    coverInput?.addEventListener('change', syncCover);
    syncCover();
})();
</script>
@endsection
