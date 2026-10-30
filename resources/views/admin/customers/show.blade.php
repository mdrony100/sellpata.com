@extends('admin.layout')

@section('title', $user->name)

@section('content')
    <p><a href="{{ route('admin.customers.index') }}">← সব গ্রাহক</a></p>
    <h1>{{ $user->name }}</h1>
    <p class="lede">{{ $user->phone }} @if ($user->email) · {{ $user->email }} @endif</p>

    @if (session('status'))
        <div class="card" style="border-color:var(--ok);margin-bottom:16px">{{ session('status') }}</div>
    @endif

    <div class="grid">
        <div style="display:grid;gap:16px;min-width:0">
            <div class="card">
                <h2>স্টোর</h2>
                @if ($store)
                    <p><strong>{{ $store->display_name }}</strong><br>
                        <a href="{{ $store->publicUrl() }}" target="_blank" rel="noopener">{{ $store->username }}.sellpata.com ↗</a>
                    </p>
                    <p>প্ল্যান: <strong>{{ $store->plan()->name }}</strong>
                        @php $sub = $store->activeSubscription(); @endphp
                        @if ($sub) · {{ $sub->source === 'manual' ? 'ম্যানুয়াল' : ($sub->source === 'free' ? 'ফ্রি' : 'পেমেন্ট') }} @endif
                        · অবস্থা: {{ $store->status === 'active' ? 'সক্রিয়' : 'স্থগিত' }}
                    </p>
                    @if ($store->suspended_reason)
                        <p style="color:var(--danger)">কারণ: {{ $store->suspended_reason }}</p>
                    @endif

                    <h2 style="margin-top:18px">ল্যান্ডিং পেজ</h2>
                    <table style="width:100%;border-collapse:collapse;font-size:.95rem">
                        <tbody>
                        @foreach ($store->pages()->get() as $page)
                            <tr style="border-top:1px solid var(--line)">
                                <td style="padding:6px 0">{{ $page->title }}</td>
                                <td style="padding:6px 0">/{{ $page->slug }}</td>
                                <td style="padding:6px 0">{{ $page->status === 'published' ? 'প্রকাশিত' : 'ড্রাফট' }}</td>
                                <td style="padding:6px 0">{{ $page->is_primary ? 'মূল পেজ' : '' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <h2 style="margin-top:18px">সাম্প্রতিক অর্ডার</h2>
                    @forelse ($store->orders()->latest()->limit(8)->get() as $order)
                        <p style="margin:4px 0">{{ $order->reference }} · {{ $order->customer_name }} · ৳{{ number_format($order->total) }} · {{ $order->status }}</p>
                    @empty
                        <p class="lede">এখনো কোনো অর্ডার নেই।</p>
                    @endforelse
                @else
                    <p class="lede">এই গ্রাহক এখনো স্টোর তৈরি করেননি।</p>
                @endif
            </div>
        </div>

        <div style="display:grid;gap:16px;align-content:start;min-width:0">
            @if ($store)
                <div class="card">
                    <h2>প্ল্যান নির্ধারণ (অডিটসহ)</h2>
                    <form method="POST" action="{{ route('admin.customers.plan', $store) }}">
                        @csrf
                        <select name="plan_id" style="width:100%;padding:9px 12px;border:1px solid var(--line);border-radius:10px">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected($store->plan()->id === $plan->id)>{{ $plan->name }} ({{ $plan->max_pages }} পেজ)</option>
                            @endforeach
                        </select>
                        <input name="note" placeholder="কারণ / নোট (আবশ্যক)" required
                               style="width:100%;margin-top:8px;padding:9px 12px;border:1px solid var(--line);border-radius:10px">
                        <button class="btn btn--primary" type="submit" style="margin-top:8px;padding:9px 16px">সংরক্ষণ করুন</button>
                    </form>
                </div>

                <div class="card">
                    <h2>স্টোর নিয়ন্ত্রণ</h2>
                    @if ($store->status === 'active')
                        <form method="POST" action="{{ route('admin.customers.suspend', $store) }}">
                            @csrf
                            <input name="reason" placeholder="স্থগিত করার কারণ (আবশ্যক)" required
                                   style="width:100%;padding:9px 12px;border:1px solid var(--line);border-radius:10px">
                            <button type="submit" style="margin-top:8px;padding:9px 16px;background:var(--danger);color:#fff;border:0;border-radius:10px;cursor:pointer">স্টোর স্থগিত করুন</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.customers.reactivate', $store) }}">
                            @csrf
                            <button type="submit" style="padding:9px 16px;background:var(--ok);color:#fff;border:0;border-radius:10px;cursor:pointer">আবার চালু করুন</button>
                        </form>
                    @endif
                    <small style="display:block;margin-top:8px;color:var(--muted)">স্থগিত করলে পাবলিক স্টোর বন্ধ হয়, কিন্তু পেজ, অর্ডার ও বিলিং ইতিহাস থাকে।</small>
                </div>
            @endif

            <div class="card">
                <h2>গ্রাহক হিসেবে দেখুন</h2>
                <input id="imp-reason" placeholder="কারণ (আবশ্যক)" style="width:100%;padding:9px 12px;border:1px solid var(--line);border-radius:10px">
                <button id="imp-start" type="button" style="margin-top:8px;padding:9px 16px;border:1px solid var(--line);background:#fff;border-radius:10px;cursor:pointer">গ্রাহক হিসেবে দেখুন</button>
                <small style="display:block;margin-top:8px;color:var(--muted)">সেশন {{ config('platform.impersonation_ttl_minutes') }} মিনিট। উচ্চ-ঝুঁকির কাজ বন্ধ থাকবে।</small>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('imp-start')?.addEventListener('click', async () => {
            const reason = document.getElementById('imp-reason').value.trim();
            if (!reason) { alert('কারণ লিখুন।'); return; }
            const response = await fetch('{{ route('admin.impersonate.start', $user) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ reason }),
            });
            if (response.ok) { window.location.assign('/dashboard'); return; }
            alert((await response.json().catch(() => ({}))).message || 'শুরু করা যায়নি।');
        });
    </script>
@endsection
