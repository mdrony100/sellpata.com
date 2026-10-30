@extends('admin.layout')

@section('title', 'অডিট লগ')

@section('content')
    <h1>অডিট ও মনিটরিং</h1>
    <p class="lede">সংবেদনশীল অ্যাডমিন কাজ ও গ্রাহক হিসাবে দেখার রেকর্ড।</p>

    <div class="grid">
        <div class="card">
            <form method="GET" style="display:flex;gap:8px;margin-bottom:14px">
                <input type="search" name="q" value="{{ $search }}" placeholder="অ্যাকশন বা ধরন"
                       style="flex:1;padding:9px 12px;border:1px solid var(--line);border-radius:10px">
                <button type="submit" style="padding:9px 16px;background:var(--brand);color:#fff;border:0;border-radius:10px;cursor:pointer">খুঁজুন</button>
            </form>

            @if ($logs->isEmpty())
                <p class="lede">এখনো কোনো রেকর্ড নেই।</p>
            @else
                <table style="width:100%;border-collapse:collapse;font-size:.92rem">
                    <thead><tr style="text-align:left;color:var(--muted);font-size:.82rem">
                        <th style="padding:8px">সময়</th><th style="padding:8px">কর্ম</th>
                        <th style="padding:8px">ব্যক্তি</th><th style="padding:8px">বিষয়</th><th style="padding:8px">IP</th>
                    </tr></thead>
                    <tbody>
                    @foreach ($logs as $log)
                        <tr style="border-top:1px solid var(--line)">
                            <td style="padding:8px">{{ $log->created_at->format('d/m H:i') }}</td>
                            <td style="padding:8px"><code>{{ $log->action }}</code></td>
                            <td style="padding:8px">{{ $log->actor?->name ?? 'সিস্টেম' }}</td>
                            <td style="padding:8px">
                                {{ class_basename($log->subject_type ?? '') }} {{ $log->subject_id }}
                                @if ($log->meta)
                                    <br><small style="color:var(--muted)">{{ \Illuminate\Support\Str::limit(json_encode($log->meta, JSON_UNESCAPED_UNICODE), 90) }}</small>
                                @endif
                            </td>
                            <td style="padding:8px"><small>{{ $log->ip }}</small></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div style="margin-top:14px">{{ $logs->links() }}</div>
            @endif
        </div>

        <div class="card">
            <h2>গ্রাহক হিসেবে দেখা</h2>
            @forelse ($impersonations as $session)
                <p style="margin:0 0 10px;font-size:.92rem">
                    <strong>{{ $session->admin?->name }}</strong> → {{ $session->user?->name }}<br>
                    <small style="color:var(--muted)">{{ $session->reason }}<br>
                        {{ $session->started_at?->format('d/m H:i') }} —
                        {{ $session->ended_at?->format('d/m H:i') ?? 'চলছে' }}</small>
                </p>
            @empty
                <p class="lede">কোনো সেশন নেই।</p>
            @endforelse
        </div>
    </div>
@endsection
