@extends('admin.layout')

@section('title', 'ল্যান্ডিং থিম')

@section('content')
    <h1>ল্যান্ডিং থিম</h1>
    <p class="lede">পাঁচটি পণ্য-বিক্রির থিম। বন্ধ করলে নতুন ড্রাফটে আর দেখায় না। প্রকাশিত পেজ আগের কনটেন্টই রাখে।</p>

    <div class="grid">
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>থিম</th>
                        <th>ক্যাটাগরি</th>
                        <th>সংস্করণ</th>
                        <th>অবস্থা</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->name }}</strong><br>
                                <span class="muted">{{ $item->tagline }}</span>
                            </td>
                            <td>
                                {{ $item->category?->label }}
                                @if ($item->suitable_for)
                                    <br><span class="muted">{{ implode(', ', $item->suitable_for) }}</span>
                                @endif
                            </td>
                            <td>{{ $item->theme_version }}</td>
                            <td>
                                <span class="pill {{ $item->is_published ? 'pill--on' : '' }}">{{ $item->is_published ? 'সক্রিয়' : 'বন্ধ' }}</span>
                            </td>
                            <td>
                                <a class="btn btn--ghost btn--sm" href="{{ route('demo', $item->slug) }}" target="_blank" rel="noopener">ডেমো</a>
                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.landing-themes.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            @if ($editing)
                <h2>{{ $editing->name }} সম্পাদনা</h2>
                <p class="muted">ডিজাইন কী <code>{{ $editing->design }}</code> বদলানো যায় না। ডিফল্ট কনটেন্ট শুধু নতুন ড্রাফটে যায়।</p>
                @if ($editing->thumbnail)
                    <img src="{{ $editing->thumbnail }}" alt="{{ $editing->name }} থাম্বনেইল" style="width:100%;border-radius:12px;margin:8px 0">
                @endif
                <form method="post" action="{{ route('admin.landing-themes.update', $editing) }}">
                    @csrf
                    @method('PUT')
                    <label for="name">বাংলা নাম</label>
                    <input id="name" name="name" value="{{ old('name', $editing->name) }}" required>

                    <label for="tagline">সংক্ষিপ্ত পরিচিতি</label>
                    <input id="tagline" name="tagline" value="{{ old('tagline', $editing->tagline) }}">

                    <label for="category_id">মূল ক্যাটাগরি</label>
                    <select id="category_id" name="category_id">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $editing->category_id) == $category->id)>{{ $category->label }}</option>
                        @endforeach
                    </select>

                    <label>মানানসই ক্যাটাগরি</label>
                    @php $picked = old('suitable_for', $editing->suitable_for ?? []); @endphp
                    @foreach ($categories->whereNotNull('parent_id') as $category)
                        <label class="check">
                            <input type="checkbox" name="suitable_for[]" value="{{ $category->slug }}" @checked(in_array($category->slug, $picked, true))>
                            {{ $category->label }}
                        </label>
                    @endforeach

                    <label for="theme_version">থিম সংস্করণ</label>
                    <input id="theme_version" name="theme_version" value="{{ old('theme_version', $editing->theme_version) }}" required>

                    <label for="sort_order">ক্রম</label>
                    <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $editing->sort_order) }}">

                    <label class="check">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $editing->is_published))>
                        সক্রিয় — নতুন ড্রাফটে দেখাবে
                    </label>

                    <label for="content_json">ডিফল্ট কনটেন্ট (JSON, খালি রাখলে আগেরটাই থাকবে)</label>
                    <textarea id="content_json" name="content_json" rows="8" placeholder="বদলাতে চাইলে পুরো JSON বসান">{{ old('content_json') }}</textarea>

                    <p style="margin-top:16px">
                        <button class="btn" type="submit">সংরক্ষণ</button>
                        <a class="btn btn--ghost" href="{{ route('admin.landing-themes.index') }}">বাতিল</a>
                    </p>
                </form>
            @else
                <h2>একটি থিম বেছে নিন</h2>
                <p class="muted">তালিকা থেকে সম্পাদনা খুললে নাম, ক্যাটাগরি, সক্রিয় অবস্থা ও ডিফল্ট কনটেন্ট বদলানো যায়।</p>
            @endif
        </div>
    </div>
@endsection
