@extends('admin.layout')

@section('title', 'প্যাকেজ')

@section('content')
    <h1>প্যাকেজ ও সীমা</h1>
    <p class="lede">দাম ও সীমা এখান থেকে নির্ধারণ করুন। দাম না দিলে গ্রাহকদের কাছে “যোগাযোগ করুন” দেখাবে, ভুয়া চেকআউট নয়।</p>

    @if (session('status'))
        <div class="card" style="border-color:var(--ok);margin-bottom:16px">{{ session('status') }}</div>
    @endif

    @foreach ($plans as $plan)
        <div class="card" style="margin-bottom:16px">
            <h2>{{ $plan->name }} <small style="color:var(--muted)">({{ $plan->slug }})</small></h2>
            <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
                @csrf @method('PATCH')
                <div style="display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
                    <label>মাসিক দাম (৳)<input type="number" name="price_monthly" min="0" value="{{ $plan->price_monthly }}" style="width:100%;padding:8px;border:1px solid var(--line);border-radius:8px"></label>
                    <label>বার্ষিক দাম (৳)<input type="number" name="price_yearly" min="0" value="{{ $plan->price_yearly }}" style="width:100%;padding:8px;border:1px solid var(--line);border-radius:8px"></label>
                    <label>পেজ সীমা<input type="number" name="max_pages" min="1" value="{{ $plan->max_pages }}" style="width:100%;padding:8px;border:1px solid var(--line);border-radius:8px"></label>
                    <label>সক্রিয়
                        <select name="is_active" style="width:100%;padding:8px;border:1px solid var(--line);border-radius:8px">
                            <option value="1" @selected($plan->is_active)>হ্যাঁ</option>
                            <option value="0" @selected(! $plan->is_active)>না</option>
                        </select>
                    </label>
                </div>

                <p style="margin:12px 0 6px;color:var(--muted);font-size:.9rem">ফিচার</p>
                <div style="display:grid;gap:6px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                    @foreach ($plan->features ?? [] as $key => $enabled)
                        <label style="display:flex;gap:6px;align-items:center;font-size:.92rem">
                            <input type="checkbox" name="features[]" value="{{ $key }}" @checked($enabled)>
                            {{ $key }}
                        </label>
                    @endforeach
                </div>

                <button type="submit" style="margin-top:12px;padding:9px 16px;background:var(--brand);color:#fff;border:0;border-radius:10px;cursor:pointer">সংরক্ষণ করুন</button>
                <small style="margin-left:8px;color:var(--muted)">
                    {{ $plan->isPurchasable() ? 'দাম নির্ধারিত — চেকআউট আলাদা ভাবে চালু করতে হবে।' : 'দাম নেই — যোগাযোগ করুন দেখাবে।' }}
                </small>
            </form>
        </div>
    @endforeach
@endsection
