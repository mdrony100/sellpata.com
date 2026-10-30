@extends('admin.layout')

@section('title', 'গ্রাহক')

@section('content')
    <h1>গ্রাহক ও স্টোর</h1>
    <p class="lede">সব গ্রাহক, তাদের স্টোর, প্ল্যান ও কার্যক্রম।</p>

    <div class="card">
        <form method="GET" action="{{ route('admin.customers.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
            <input type="search" name="q" value="{{ $search }}" placeholder="নাম, ফোন, ইমেইল বা স্টোর ঠিকানা"
                   style="flex:1 1 240px;padding:9px 12px;border:1px solid var(--line);border-radius:10px">
            <button class="btn btn--primary" type="submit" style="padding:9px 16px">খুঁজুন</button>
        </form>

        @if ($users->isEmpty())
            <p class="lede">কোনো গ্রাহক পাওয়া যায়নি।</p>
        @else
            <table style="width:100%;border-collapse:collapse;font-size:.95rem">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:.85rem">
                        <th style="padding:8px">গ্রাহক</th><th style="padding:8px">স্টোর</th>
                        <th style="padding:8px">প্ল্যান</th><th style="padding:8px">পেজ</th>
                        <th style="padding:8px">অর্ডার</th><th style="padding:8px">অবস্থা</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($users as $user)
                    <tr style="border-top:1px solid var(--line)">
                        <td style="padding:8px">
                            <strong>{{ $user->name }}</strong><br>
                            <small style="color:var(--muted)">{{ $user->phone }}</small>
                        </td>
                        <td style="padding:8px">
                            @if ($user->store)
                                {{ $user->store->username }}.sellpata.com
                                <br><small style="color:var(--muted)">{{ $user->store->display_name }}</small>
                            @else
                                <span style="color:var(--muted)">স্টোর নেই</span>
                            @endif
                        </td>
                        <td style="padding:8px">{{ $user->store?->plan()->name ?? '—' }}</td>
                        <td style="padding:8px">{{ $user->store?->pages()->count() ?? 0 }}</td>
                        <td style="padding:8px">{{ $user->store?->orders()->count() ?? 0 }}</td>
                        <td style="padding:8px">
                            @if ($user->store && $user->store->status !== 'active')
                                <span style="color:var(--danger)">স্থগিত</span>
                            @else
                                <span style="color:var(--ok)">সক্রিয়</span>
                            @endif
                        </td>
                        <td style="padding:8px"><a href="{{ route('admin.customers.show', $user) }}">বিস্তারিত</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="margin-top:14px">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
