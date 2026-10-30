@extends('admin.layout')
@section('title', 'বার্তা')
@section('content')
    <h1>যোগাযোগের বার্তা</h1>
    <p class="lede">ওয়েবসাইটের ফর্ম থেকে আসা বার্তা।</p>
    <div class="grid">
        <div class="card">
            <h2>সব বার্তা</h2>
            @if ($items->isEmpty())
                <p class="muted">এখনো কোনো বার্তা আসেনি।</p>
            @else
                @foreach ($items as $item)
                    <div class="msg {{ $item->is_handled ? '' : 'msg--new' }}">
                        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
                            <strong>{{ $item->name }} · {{ $item->phone }}</strong>
                            <span class="muted" style="font-size:.85rem">{{ $item->created_at?->format('d M Y, h:i A') }}</span>
                        </div>
                        @if ($item->page_url)
                            <div class="muted" style="font-size:.85rem">{{ $item->page_url }}</div>
                        @endif
                        <p style="margin:8px 0">{{ $item->message }}</p>
                        <div style="display:flex;gap:10px;flex-wrap:wrap">
                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.messages.index', ['show' => $item->id]) }}">বিস্তারিত</a>
                            <form method="post" action="{{ route('admin.messages.handled', $item) }}">
                                @csrf @method('patch')
                                <button class="btn btn--ghost btn--sm" type="submit">{{ $item->is_handled ? 'আবার নতুন হিসেবে রাখুন' : 'সমাধান হয়েছে' }}</button>
                            </form>
                            <form method="post" action="{{ route('admin.messages.destroy', $item) }}" onsubmit="return confirm('মুছে ফেলবেন?')">
                                @csrf @method('delete')
                                <button class="btn btn--danger" type="submit">মুছুন</button>
                            </form>
                        </div>
                    </div>
                @endforeach
                {{ $items->links() }}
            @endif
        </div>
        <div class="card">
            <h2>বিস্তারিত</h2>
            @if ($editing)
                <p><strong>{{ $editing->name }}</strong><br><span class="muted">{{ $editing->phone }}</span></p>
                @if ($editing->page_url)<p class="muted" style="font-size:.9rem">{{ $editing->page_url }}</p>@endif
                <p style="white-space:pre-line">{{ $editing->message }}</p>
                <p class="muted" style="font-size:.82rem">{{ $editing->created_at?->format('d M Y, h:i A') }}</p>
                <a class="btn btn--ghost btn--sm" href="{{ route('admin.messages.index') }}">তালিকায় ফিরুন</a>
            @else
                <p class="muted">বাম দিকের তালিকা থেকে একটি বার্তা বেছে নিন।</p>
            @endif
        </div>
    </div>
@endsection
