@extends('admin.layout')

@section('title', 'অর্ডার')

@section('content')
    <h1>সব অর্ডার</h1>
    <p class="lede">প্ল্যাটফর্মের সব স্টোরের অর্ডার — স্টোর অনুযায়ী আলাদা ভাবে ব্যবস্থাপনা হয়।</p>

    <div class="card">
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="অর্ডার নম্বর, ফোন বা নাম"
                   style="flex:1 1 220px;padding:9px 12px;border:1px solid var(--line);border-radius:10px">
            <select name="status" style="padding:9px 12px;border:1px solid var(--line);border-radius:10px">
                <option value="">সব অবস্থা</option>
                @foreach (\App\Models\Order::STATUSES as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button type="submit" style="padding:9px 16px;background:var(--brand);color:#fff;border:0;border-radius:10px;cursor:pointer">খুঁজুন</button>
        </form>

        @if ($orders->isEmpty())
            <p class="lede">কোনো অর্ডার পাওয়া যায়নি।</p>
        @else
            <table style="width:100%;border-collapse:collapse;font-size:.95rem">
                <thead><tr style="text-align:left;color:var(--muted);font-size:.85rem">
                    <th style="padding:8px">অর্ডার</th><th style="padding:8px">স্টোর</th><th style="padding:8px">ক্রেতা</th>
                    <th style="padding:8px">মোট</th><th style="padding:8px">অবস্থা</th><th style="padding:8px">পেমেন্ট</th><th></th>
                </tr></thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr style="border-top:1px solid var(--line)">
                        <td style="padding:8px">{{ $order->reference }}</td>
                        <td style="padding:8px">{{ $order->store?->username }}</td>
                        <td style="padding:8px">{{ $order->customer_name }}<br><small style="color:var(--muted)">{{ $order->customer_phone }}</small></td>
                        <td style="padding:8px">৳{{ number_format($order->total) }}</td>
                        <td style="padding:8px">{{ $order->status }}</td>
                        <td style="padding:8px">{{ $order->payment_status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}</td>
                        <td style="padding:8px"><a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" rel="noopener">ইনভয়েস</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="margin-top:14px">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
