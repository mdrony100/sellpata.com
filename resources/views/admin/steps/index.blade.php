@extends('admin.layout')
@section('title', 'ধাপ')
@section('content')
    <h1>ধাপ</h1>
    <p class="lede">“বেছে নিন। সাজান। শুরু করুন।” সেকশনের তিনটি ধাপ।</p>
    <div class="grid">
        <div class="card">
            <h2>সব ধাপ</h2>
            <table>
                <thead><tr><th>ক্রম</th><th>শিরোনাম</th><th>বর্ণনা</th><th></th></tr></thead>
                <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->number_label }}</td>
                        <td>{{ $item->title }}</td>
                        <td class="muted">{{ \Illuminate\Support\Str::limit($item->body, 60) }}</td>
                        <td style="white-space:nowrap">
                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.steps.index', ['edit' => $item->id]) }}">সম্পাদনা</a>
                            <form method="post" action="{{ route('admin.steps.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
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
            <h2>{{ $editing ? 'ধাপ সম্পাদনা' : 'নতুন ধাপ' }}</h2>
            <form method="post" action="{{ $editing ? route('admin.steps.update', $editing) : route('admin.steps.store') }}">
                @csrf
                @if ($editing) @method('put') @endif
                <div class="row">
                    <div>
                        <label for="number_label">নম্বর</label>
                        <input id="number_label" type="text" name="number_label" value="{{ old('number_label', $editing->number_label ?? '১') }}" required>
                    </div>
                    <div>
                        <label for="sort_order">ক্রম</label>
                        <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $editing->sort_order ?? 0) }}">
                    </div>
                </div>
                <label for="title">শিরোনাম</label>
                <input id="title" type="text" name="title" value="{{ old('title', $editing->title ?? '') }}" required>
                <label for="body">বর্ণনা</label>
                <textarea id="body" name="body" required>{{ old('body', $editing->body ?? '') }}</textarea>
                <div class="check">
                    <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $editing->is_published ?? true))>
                    <label for="is_published" style="margin:0">সাইটে দেখাও</label>
                </div>
                <p style="margin-top:16px;display:flex;gap:10px">
                    <button class="btn" type="submit">{{ $editing ? 'আপডেট করুন' : 'যোগ করুন' }}</button>
                    @if ($editing)<a class="btn btn--ghost" href="{{ route('admin.steps.index') }}">বাতিল</a>@endif
                </p>
            </form>
        </div>
    </div>
@endsection
