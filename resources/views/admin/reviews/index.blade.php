@extends('admin.layout')
@section('title', 'রিভিউ')
@section('content')
    <h1>রিভিউ</h1>
    <p class="lede">শুধু সত্যিকারের গ্রাহকের মতামত যোগ করুন। কোনো রিভিউ না থাকলে সাইটে “শিগগিরই যুক্ত হবে” দেখায়।</p>
    <div class="grid">
        <div class="card">
            <h2>সব রিভিউ</h2>
            @if ($items->isEmpty())
                <p class="muted">এখনো কোনো রিভিউ নেই।</p>
            @else
                <table>
                    <thead><tr><th>নাম</th><th>রেটিং</th><th>মতামত</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->author }}<div class="muted" style="font-size:.82rem">{{ $item->brand }}</div></td>
                            <td>{{ $item->rating ? str_repeat('★', $item->rating) : '—' }}</td>
                            <td class="muted">{{ \Illuminate\Support\Str::limit($item->body, 60) }}</td>
                            <td style="white-space:nowrap">
                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.reviews.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                                <form method="post" action="{{ route('admin.reviews.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
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
            <h2>{{ $editing ? 'রিভিউ সম্পাদনা' : 'নতুন রিভিউ' }}</h2>
            <form method="post" action="{{ $editing ? route('admin.reviews.update', $editing) : route('admin.reviews.store') }}">
                @csrf
                @if ($editing) @method('put') @endif
                <label for="author">গ্রাহকের নাম</label>
                <input id="author" type="text" name="author" value="{{ old('author', $editing->author ?? '') }}" required>
                <label for="brand">ব্র্যান্ড</label>
                <input id="brand" type="text" name="brand" value="{{ old('brand', $editing->brand ?? '') }}">
                <label for="body">মতামত</label>
                <textarea id="body" name="body" required>{{ old('body', $editing->body ?? '') }}</textarea>
                <label for="rating">রেটিং (১–৫)</label>
                <input id="rating" type="number" name="rating" min="1" max="5" value="{{ old('rating', $editing->rating ?? '') }}">
                <div class="check">
                    <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $editing->is_published ?? true))>
                    <label for="is_published" style="margin:0">সাইটে দেখাও</label>
                </div>
                <p style="margin-top:16px;display:flex;gap:10px">
                    <button class="btn" type="submit">{{ $editing ? 'আপডেট করুন' : 'যোগ করুন' }}</button>
                    @if ($editing)<a class="btn btn--ghost" href="{{ route('admin.reviews.index') }}">বাতিল</a>@endif
                </p>
            </form>
        </div>
    </div>
@endsection
