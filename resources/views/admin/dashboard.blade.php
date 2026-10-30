@extends('admin.layout')
@section('title', 'ড্যাশবোর্ড')
@section('content')
    <h1>ড্যাশবোর্ড</h1>
    <p class="lede">ওয়েবসাইটের সব কনটেন্ট এখান থেকে পরিচালনা করুন। পরিবর্তন সাথে সাথেই সাইটে দেখা যাবে।</p>

    <div class="stat">
        <div class="card"><span class="muted">টেমপ্লেট</span><strong>{{ $counts['templates'] }}</strong></div>
        <div class="card"><span class="muted">ক্যাটাগরি</span><strong>{{ $counts['categories'] }}</strong></div>
        <div class="card"><span class="muted">সুবিধা</span><strong>{{ $counts['benefits'] }}</strong></div>
        <div class="card"><span class="muted">ধাপ</span><strong>{{ $counts['steps'] }}</strong></div>
        <div class="card"><span class="muted">ক্লায়েন্ট</span><strong>{{ $counts['clients'] }}</strong></div>
        <div class="card"><span class="muted">রিভিউ</span><strong>{{ $counts['reviews'] }}</strong></div>
        <div class="card"><span class="muted">বার্তা</span><strong>{{ $counts['messages'] }}</strong></div>
        <div class="card"><span class="muted">নতুন বার্তা</span><strong>{{ $counts['unread'] }}</strong></div>
    </div>

    <div class="grid" style="margin-top:22px">
        <div class="card">
            <h2>দ্রুত কাজ</h2>
            <p class="muted">বাংলা কনটেন্ট এখানেই বদলান, কোড ছোঁয়ার দরকার নেই।</p>
            <p style="display:flex;flex-wrap:wrap;gap:10px">
                <a class="btn" href="{{ route('admin.templates.index') }}">টেমপ্লেট যোগ করুন</a>
                <a class="btn btn--ghost" href="{{ route('admin.clients.index') }}">ক্লায়েন্ট যোগ করুন</a>
                <a class="btn btn--ghost" href="{{ route('admin.settings.index') }}">সেটিংস</a>
            </p>
        </div>
        <div class="card">
            <h2>মনে রাখবেন</h2>
            <p class="muted">পেমেন্ট ও শিপিং “শিগগিরই আসছে” দেখাবে যতক্ষণ সেটিংসে চালু না করবেন। সাবডোমেইন ও ডোমেইন এখনো প্রস্তুত নয়—সাইটে সেগুলো উদাহরণ হিসেবে দেখানো হয়।</p>
        </div>
    </div>
@endsection
