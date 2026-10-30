@extends('admin.layout')
@section('title', 'সুবিধা')
@section('content')
    <h1>সুবিধা</h1>
    <p class="lede">হোমপেজের সুবিধা কার্ডগুলো। পেমেন্ট ও শিপিং কার্ডে “শিগগিরই আসছে” লেবেল সেটিংস থেকে নিয়ন্ত্রিত।</p>
    <div class="grid">
        <div class="card">
            <h2>সব সুবিধা</h2>
            <table>
                <thead><tr><th>শিরোনাম</th><th>আইকন</th><th>ফিচার</th><th>ক্রম</th><th></th></tr></thead>
                <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->title }}<div class="muted" style="font-size:.82rem">{{ \Illuminate\Support\Str::limit($item->body, 60) }}</div></td>
                        <td class="muted">{{ $item->icon }}</td>
                        <td class="muted">{{ $item->feature_key ?? '—' }}</td>
                        <td>{{ $item->sort_order }}</td>
                        <td style="white-space:nowrap">
                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.benefits.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                            <form method="post" action="{{ route('admin.benefits.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
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
            <h2>{{ $editing ? 'সুবিধা সম্পাদনা' : 'নতুন সুবিধা' }}</h2>
            <form method="post" action="{{ $editing ? route('admin.benefits.update', $editing) : route('admin.benefits.store') }}">
                @csrf
                @if ($editing) @method('put') @endif
                <label for="title">শিরোনাম</label>
                <input id="title" type="text" name="title" value="{{ old('title', $editing->title ?? '') }}" required>
                <label for="body">বর্ণনা</label>
                <textarea id="body" name="body" required>{{ old('body', $editing->body ?? '') }}</textarea>
                <div class="row">
                    <div>
                        <label for="icon">আইকন</label>
                        <select id="icon" name="icon">
                            @foreach (['puzzle' => 'ধাঁধা', 'grid' => 'গ্রিড', 'sliders' => 'স্লাইডার', 'card' => 'কার্ড', 'truck' => 'ট্রাক', 'spark' => 'স্পার্ক'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('icon', $editing->icon ?? 'spark') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="feature_key">ফিচার নির্ভরতা</label>
                        <select id="feature_key" name="feature_key">
                            <option value="">কোনোটি নয়</option>
                            <option value="payments" @selected(old('feature_key', $editing->feature_key ?? '') === 'payments')>পেমেন্ট</option>
                            <option value="shipping" @selected(old('feature_key', $editing->feature_key ?? '') === 'shipping')>শিপিং</option>
                        </select>
                    </div>
                </div>
                <label for="sort_order">ক্রম</label>
                <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $editing->sort_order ?? 0) }}">
                <div class="check">
                    <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $editing->is_published ?? true))>
                    <label for="is_published" style="margin:0">সাইটে দেখাও</label>
                </div>
                <p style="margin-top:16px;display:flex;gap:10px">
                    <button class="btn" type="submit">{{ $editing ? 'আপডেট করুন' : 'যোগ করুন' }}</button>
                    @if ($editing)<a class="btn btn--ghost" href="{{ route('admin.benefits.index') }}">বাতিল</a>@endif
                </p>
            </form>
        </div>
    </div>
@endsection
