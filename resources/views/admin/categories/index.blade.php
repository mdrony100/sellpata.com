@extends('admin.layout')
@section('title', 'ক্যাটাগরি')
@section('content')
    <h1>ক্যাটাগরি</h1>
    <p class="lede">হোমপেজের ফিল্টার ও টেমপ্লেটের গ্রুপ এখান থেকে নিয়ন্ত্রণ করুন।</p>
    <div class="grid">
        <div class="card">
            <h2>সব ক্যাটাগরি</h2>
            <table>
                <thead><tr><th>নাম</th><th>স্লাগ</th><th>ক্রম</th><th>অবস্থা</th><th></th></tr></thead>
                <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->label }}</td>
                        <td class="muted">{{ $item->slug }}</td>
                        <td>{{ $item->sort_order }}</td>
                        <td><span class="pill {{ $item->is_visible ? 'pill--on' : '' }}">{{ $item->is_visible ? 'দৃশ্যমান' : 'লুকানো' }}</span></td>
                        <td style="white-space:nowrap">
                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.categories.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                            <form method="post" action="{{ route('admin.categories.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
                                @csrf @method('delete')
                                <button class="btn btn--danger" type="submit">মুছুন</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card">
            <h2>{{ $editing ? 'ক্যাটাগরি সম্পাদনা' : 'নতুন ক্যাটাগরি' }}</h2>
            <form method="post" action="{{ $editing ? route('admin.categories.update', $editing) : route('admin.categories.store') }}">
                @csrf
                @if ($editing) @method('put') @endif
                <label for="label">নাম (বাংলা)</label>
                <input id="label" type="text" name="label" value="{{ old('label', $editing->label ?? '') }}" required>
                <label for="slug">স্লাগ (ইংরেজি)</label>
                <input id="slug" type="text" name="slug" value="{{ old('slug', $editing->slug ?? '') }}" required placeholder="fashion">
                <label for="sort_order">ক্রম</label>
                <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $editing->sort_order ?? 0) }}">
                <div class="check">
                    <input id="is_visible" type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $editing->is_visible ?? true))>
                    <label for="is_visible" style="margin:0">সাইটে দেখাও</label>
                </div>
                <p style="margin-top:16px;display:flex;gap:10px">
                    <button class="btn" type="submit">{{ $editing ? 'আপডেট করুন' : 'যোগ করুন' }}</button>
                    @if ($editing)
                        <a class="btn btn--ghost" href="{{ route('admin.categories.index') }}">বাতিল</a>
                    @endif
                </p>
            </form>
        </div>
    </div>
@endsection
