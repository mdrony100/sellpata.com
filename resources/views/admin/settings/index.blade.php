@extends('admin.layout')
@section('title', 'সেটিংস')
@section('content')
    <h1>সেটিংস</h1>
    <p class="lede">ব্র্যান্ড, ভিডিও, ফিচার ও যোগাযোগের তথ্য। খালি রাখলে সাইটে সেটি দেখানো হবে না।</p>
    <form method="post" action="{{ route('admin.settings.update') }}">
        @csrf @method('put')
        <div class="grid">
            <div class="card">
                <h2>ব্র্যান্ড</h2>
                <label for="brand_name">ব্র্যান্ডের নাম</label>
                <input id="brand_name" type="text" name="brand[name]" value="{{ $values['brand.name'] }}" required>
                <label for="brand_domain">ডোমেইন</label>
                <input id="brand_domain" type="text" name="brand[domain]" value="{{ $values['brand.domain'] }}" required>
                <label for="brand_tagline">সংক্ষিপ্ত পরিচিতি (ফুটারে দেখানো হয়)</label>
                <textarea id="brand_tagline" name="brand[tagline]" required>{{ $values['brand.tagline'] }}</textarea>

                <h2 style="margin-top:22px">হিরো ভিডিও</h2>
                <label for="youtube_url">ইউটিউব লিংক</label>
                <input id="youtube_url" type="url" name="youtube_url" value="{{ $values['youtube_url'] }}" placeholder="https://www.youtube.com/watch?v=...">
                <p class="muted" style="font-size:.85rem">খালি রাখলে বাংলায় “ভিডিও শিগগিরই যুক্ত হবে” দেখাবে।</p>

                <label for="template_count_label">টেমপ্লেট সংখ্যার লেবেল</label>
                <input id="template_count_label" type="text" name="template_count_label" value="{{ $values['template_count_label'] }}" placeholder="৫০০+ রেডিমেড ল্যান্ডিং পেজ">
                <p class="muted" style="font-size:.85rem">খালি রাখলে “রেডমেড ল্যান্ডিং পেজের সংগ্রহ” দেখাবে। সংখ্যা নিশ্চিত না হলে খালিই রাখুন।</p>
            </div>

            <div class="card">
                <h2>ফিচার</h2>
                <div class="check">
                    <input id="payments" type="checkbox" name="features[payments][available]" value="1" @checked($values['features.payments.available'] === '1')>
                    <label for="payments" style="margin:0">পেমেন্ট গেটওয়ে চালু আছে</label>
                </div>
                <div class="check">
                    <input id="shipping" type="checkbox" name="features[shipping][available]" value="1" @checked($values['features.shipping.available'] === '1')>
                    <label for="shipping" style="margin:0">শিপিং সার্ভিস চালু আছে</label>
                </div>
                <label for="coming_soon">চালু না থাকলে যে লেবেল দেখাবে</label>
                <input id="coming_soon" type="text" name="features[coming_soon_label]" value="{{ $values['features.coming_soon_label'] }}" required>

                <h2 style="margin-top:22px">যোগাযোগ</h2>
                <label for="phone">মোবাইল নম্বর</label>
                <input id="phone" type="text" name="contact[phone]" value="{{ $values['contact.phone'] }}">
                <label for="email">ইমেইল</label>
                <input id="email" type="email" name="contact[email]" value="{{ $values['contact.email'] }}">
                <label for="whatsapp">হোয়াটসঅ্যাপ নম্বর</label>
                <input id="whatsapp" type="text" name="contact[whatsapp]" value="{{ $values['contact.whatsapp'] }}">
                <p class="muted" style="font-size:.85rem">যেটি খালি থাকবে সেটি সাইটে দেখানো হবে না।</p>

                <h2 style="margin-top:22px">প্ল্যাটফর্ম</h2>
                <label for="base_domain">বেস ডোমেইন (সাবডোমেইন এর নিচে বসবে)</label>
                <input id="base_domain" type="text" name="platform[base_domain]" value="{{ $values['platform.base_domain'] }}" required>
                <p class="muted" style="font-size:.85rem">উদাহরণ: sellpata.com — গ্রাহকের ঠিকানা হবে username.sellpata.com।</p>

                <label for="ttl">“গ্রাহক হিসেবে দেখুন” সেশনের সময় (মিনিট)</label>
                <input id="ttl" type="number" min="5" max="240" name="platform[impersonation_ttl_minutes]" value="{{ $values['platform.impersonation_ttl_minutes'] }}" required>

                <h2 style="margin-top:22px">ইনভয়েসে যা ছাপা হবে</h2>
                <label for="issuer_name">প্রদানকারীর নাম</label>
                <input id="issuer_name" type="text" name="platform[invoice_issuer_name]" value="{{ $values['platform.invoice.issuer.name'] }}" required>
                <label for="issuer_address">ঠিকানা</label>
                <input id="issuer_address" type="text" name="platform[invoice_issuer_address]" value="{{ $values['platform.invoice.issuer.address'] }}">
                <label for="issuer_phone">ফোন</label>
                <input id="issuer_phone" type="text" name="platform[invoice_issuer_phone]" value="{{ $values['platform.invoice.issuer.phone'] }}">
                <label for="issuer_email">ইমেইল</label>
                <input id="issuer_email" type="email" name="platform[invoice_issuer_email]" value="{{ $values['platform.invoice.issuer.email'] }}">
                <label for="vat_note">ভ্যাট / কর সংক্রান্ত নোট</label>
                <input id="vat_note" type="text" name="platform[invoice_vat_note]" value="{{ $values['platform.invoice.vat_note'] }}" placeholder="যেমন: মূল্য সংযোজিত কর প্রযোজ্য নয়">
                <p class="muted" style="font-size:.85rem">খালি রাখলে ইনভয়েসে ওই লাইনটি ছাপা হবে না।</p>

                <p style="margin-top:20px">
                    <button class="btn" type="submit">সংরক্ষণ করুন</button>
                </p>
            </div>
        </div>
    </form>
@endsection
