<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        @font-face { font-family: 'HindSiliguri'; src: url('{{ $fontUrl }}') format('truetype'); font-weight: 400; }
        @page { size: A4; margin: 16mm 14mm; }
        body { font-family: 'HindSiliguri', sans-serif; font-size: 11pt; color: #1b2320; }
        h1 { font-size: 18pt; margin: 0 0 4px; }
        .muted { color: #5d6763; font-size: 9.5pt; }
        .head { border-bottom: 2px solid #0d3b2e; padding-bottom: 10px; margin-bottom: 14px; }
        .row { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { text-align: left; background: #f3eee2; padding: 7px; font-size: 10pt; }
        td { padding: 7px; border-bottom: 1px solid #e2dccd; }
        .total { font-size: 13pt; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 10px; background: #e8f9b8; font-size: 9pt; }
    </style>
</head>
<body>
<div class="head">
    <h1>{{ $invoice->type === 'order' ? 'অর্ডার ইনভয়েস' : 'সাবস্ক্রিপশন ইনভয়েস' }}</h1>
    <p class="muted">ইনভয়েস নম্বর: {{ $invoice->number }} · তারিখ: {{ $invoice->issued_at->format('d/m/Y') }}</p>
</div>

<div class="row">
    <div>
        <p class="muted">প্রদানকারী</p>
        <p><strong>{{ $invoice->seller_snapshot['name'] ?? '' }}</strong><br>
        @if (! empty($invoice->seller_snapshot['address'])) {{ $invoice->seller_snapshot['address'] }}<br> @endif
        @if (! empty($invoice->seller_snapshot['phone'])) {{ $invoice->seller_snapshot['phone'] }}<br> @endif
        @if (! empty($invoice->seller_snapshot['url'])) {{ $invoice->seller_snapshot['url'] }} @endif
        </p>
    </div>
    <div>
        <p class="muted">গ্রাহক</p>
        <p><strong>{{ $invoice->buyer_snapshot['name'] ?? '' }}</strong><br>
        @if (! empty($invoice->buyer_snapshot['phone'])) {{ $invoice->buyer_snapshot['phone'] }}<br> @endif
        @if (! empty($invoice->buyer_snapshot['address'])) {{ $invoice->buyer_snapshot['address'] }}<br> @endif
        @if (! empty($invoice->buyer_snapshot['city'])) {{ $invoice->buyer_snapshot['city'] }} @endif
        </p>
    </div>
</div>

@if ($order)
    <table>
        <thead>
            <tr><th>পণ্য</th><th>পরিমাণ</th><th>একক দাম</th><th>মোট</th></tr>
        </thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>৳ {{ number_format($item->unit_price) }}</td>
                <td>৳ {{ number_format($item->line_total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="muted">ডেলিভারি চার্জ: ৳ {{ number_format($order->delivery_charge) }}
        · ডেলিভারি ঠিকানা: {{ $order->address }}</p>
@else
    <table>
        <thead><tr><th>প্ল্যান</th><th>বিলিং সময়কাল</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $plan?->name ?? '—' }}</td>
                <td>{{ $invoice->subscription?->billing_period === 'yearly' ? 'বার্ষিক' : 'মাসিক' }}</td>
            </tr>
        </tbody>
    </table>
@endif

<p class="total">সর্বমোট: ৳ {{ number_format($invoice->amount) }}</p>
<p>পরিশোধের অবস্থা: <span class="badge">{{ ['unpaid' => 'বাকি', 'paid' => 'পরিশোধিত', 'refunded' => 'ফেরত', 'void' => 'বাতিল'][$invoice->status] ?? $invoice->status }}</span>
@if ($order) · পেমেন্ট মাধ্যম: ক্যাশ অন ডেলিভারি @endif
</p>
@if ($vatNote)
    <p class="muted">{{ $vatNote }}</p>
@endif
<p class="muted">এই ইনভয়েসটি SellPata প্ল্যাটফর্ম থেকে স্বয়ংক্রিয়ভাবে তৈরি।</p>
</body>
</html>
