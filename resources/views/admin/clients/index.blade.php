@extends('admin.layout')
@section('title', 'ক্লায়েন্ট')
@section('content')
    <h1>ক্লায়েন্ট</h1>
    <p class="lede">প্রকৃত ক্লায়েন্টের ল্যান্ডিং পেজ এখানে যোগ করুন। খালি থাকলে সাইটে সৎ “শূন্য অবস্থা” দেখায়, বানানো ক্লায়েন্ট নয়।</p>
    <div class="grid">
        <div class="card">
            <h2>সব ক্লায়েন্ট</h2>
            @if ($items->isEmpty())
                <p class="muted">এখনো কোনো ক্লায়েন্ট যোগ করা হয়নি।</p>
            @else
                <table>
                    <thead><tr><th>ব্র্যান্ড</th><th>ক্যাটাগরি</th><th>লিংক</th><th>স্ট্যাটাস</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->brand }}</td>
                            <td class="muted">{{ $item->category ?? '—' }}</td>
                            <td class="muted">{{ $item->url ? \Illuminate\Support\Str::limit($item->url, 28) : '—' }}</td>
                            <td><span class="pill {{ $item->is_published ? 'pill--on' : '' }}">{{ $item->is_published ? 'প্রকাশিত' : 'খসড়া' }}</span></td>
                            <td style="white-space:nowrap">
                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.clients.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                                <form method="post" action="{{ route('admin.clients.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
                                    @csrf @method('delete')
                                    <button class="btn btn--danger" type="submit">মুছুন</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="card">
            <h2>{{ $editing ? 'ক্লায়েন্ট সম্পাদনা' : 'নতুন ক্লায়েন্ট' }}</h2>
            <form method="post" action="{{ $editing ? route('admin.clients.update', $editing) : route('admin.clients.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($editing) @method('put') @endif
                <label for="brand">ব্র্যান্ডের নাম</label>
                <input id="brand" type="text" name="brand" value="{{ old('brand', $editing->brand ?? '') }}" required>
                <label for="category">ক্যাটাগরি</label>
                <input id="category" type="text" name="category" value="{{ old('category', $editing->category ?? '') }}" placeholder="ফ্যাশন ও পোশাক">
                <label for="url">লাইভ সাইটের লিংক</label>
                <input id="url" type="url" name="url" value="{{ old('url', $editing->url ?? '') }}" placeholder="https://example.com">
                <label for="image">প্রিভিউ ছবি (jpg/png/webp, সর্বোচ্চ ২MB)</label>
                <input id="image" type="file" name="image" accept="image/*">
                @if ($editing?->image_path)
                    <p class="muted" style="font-size:.85rem">বর্তমান ছবি আছে। নতুন ছবি দিলে সেটি বদলে যাবে।</p>
                @endif
                <label for="sort_order">ক্রম</label>
                <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $editing->sort_order ?? 0) }}">
                <div class="check">
                    <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $editing->is_published ?? true))>
                    <label for="is_published" style="margin:0">সাইটে দেখাও</label>
                </div>
                <p style="margin-top:16px;display:flex;gap:10px">
                    <button class="btn" type="submit">{{ $editing ? 'আপডেট করুন' : 'যোগ করুন' }}</button>
                    @if ($editing)<a class="btn btn--ghost" href="{{ route('admin.clients.index') }}">বাতিল</a>@endif
                </p>
            </form>
        </div>
    </div>
@endsection
