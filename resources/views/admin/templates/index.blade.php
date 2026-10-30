@extends('admin.layout')
@section('title', 'টেমপ্লেট')
@section('content')
    <h1>টেমপ্লেট</h1>
    <p class="lede">হোমপেজের “নমুনা ডিজাইন” কার্ডগুলো। রঙ ও লেখা বদলে নিজের ব্র্যান্ডের স্বাদ দিন।</p>
    <div class="grid">
        <div class="card">
            <h2>সব টেমপ্লেট</h2>
            <table>
                <thead><tr><th>ব্র্যান্ড</th><th>ক্যাটাগরি</th><th>দাম</th><th>ক্রম</th><th>অবস্থা</th><th></th></tr></thead>
                <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->brand }}<div class="muted" style="font-size:.82rem">{{ $item->headline }}</div></td>
                        <td class="muted">{{ $item->category?->label }}</td>
                        <td>{{ $item->price }}</td>
                        <td>{{ $item->sort_order }}</td>
                        <td><span class="pill {{ $item->is_published ? 'pill--on' : '' }}">{{ $item->is_published ? 'প্রকাশিত' : 'খসড়া' }}</span></td>
                        <td style="white-space:nowrap">
                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.templates.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                            <form method="post" action="{{ route('admin.templates.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
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
            <h2>{{ $editing ? 'টেমপ্লেট সম্পাদনা' : 'নতুন টেমপ্লেট' }}</h2>
            @php $palette = old('palette', $editing->palette ?? ['bg' => '#f6efe3', 'surface' => '#ffffff', 'ink' => '#22201d', 'primary' => '#155e47', 'accent' => '#c9f24d']); @endphp
            <form method="post" action="{{ $editing ? route('admin.templates.update', $editing) : route('admin.templates.store') }}">
                @csrf
                @if ($editing) @method('put') @endif
                <label for="category_id">ক্যাটাগরি</label>
                <select id="category_id" name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $editing->category_id ?? 0) === $category->id)>{{ $category->label }}</option>
                    @endforeach
                </select>
                <label for="brand">ব্র্যান্ডের নাম</label>
                <input id="brand" type="text" name="brand" value="{{ old('brand', $editing->brand ?? '') }}" required>
                <label for="headline">হেডলাইন</label>
                <input id="headline" type="text" name="headline" value="{{ old('headline', $editing->headline ?? '') }}" required>
                <label for="subline">সাব-লাইন</label>
                <input id="subline" type="text" name="subline" value="{{ old('subline', $editing->subline ?? '') }}">
                <div class="row">
                    <div>
                        <label for="product_name">পণ্যের নাম</label>
                        <input id="product_name" type="text" name="product_name" value="{{ old('product_name', $editing->product_name ?? '') }}">
                    </div>
                    <div>
                        <label for="price">দাম</label>
                        <input id="price" type="text" name="price" value="{{ old('price', $editing->price ?? '') }}" placeholder="৯৯০৳">
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label for="old_price">আগের দাম</label>
                        <input id="old_price" type="text" name="old_price" value="{{ old('old_price', $editing->old_price ?? '') }}">
                    </div>
                    <div>
                        <label for="badge">ব্যাজ</label>
                        <input id="badge" type="text" name="badge" value="{{ old('badge', $editing->badge ?? '') }}" placeholder="নতুন">
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label for="art">ছবির ধরন</label>
                        <select id="art" name="art">
                            @foreach (['fashion' => 'পোশাক', 'beauty' => 'বিউটি', 'food' => 'খাবার', 'gadget' => 'গ্যাজেট', 'home' => 'ঘর', 'kids' => 'শিশু', 'gift' => 'গিফট'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('art', $editing->art ?? 'gift') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sort_order">ক্রম</label>
                        <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $editing->sort_order ?? 0) }}">
                    </div>
                </div>

                <label>রঙ</label>
                <div class="row">
                    @foreach (['bg' => 'ব্যাকগ্রাউন্ড', 'surface' => 'সারফেস', 'ink' => 'টেক্সট', 'primary' => 'প্রাইমারি', 'accent' => 'অ্যাকসেন্ট'] as $key => $label)
                        <div>
                            <input type="text" name="palette[{{ $key }}]" value="{{ $palette[$key] ?? '#ffffff' }}" aria-label="{{ $label }}">
                            <label style="margin-top:2px">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="check">
                    <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $editing->is_published ?? true))>
                    <label for="is_published" style="margin:0">সাইটে দেখাও</label>
                </div>
                <p style="margin-top:16px;display:flex;gap:10px">
                    <button class="btn" type="submit">{{ $editing ? 'আপডেট করুন' : 'যোগ করুন' }}</button>
                    @if ($editing)<a class="btn btn--ghost" href="{{ route('admin.templates.index') }}">বাতিল</a>@endif
                </p>
            </form>
        </div>
    </div>
@endsection
