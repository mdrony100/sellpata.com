<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d3b2e">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SellPata — ফেসবুকের ব্যবসার জন্য নিজের ব্র্যান্ডের ল্যান্ডিং পেজ')</title>
    <meta name="description" content="@yield('description', 'ফেসবুক উদ্যোক্তাদের জন্য রেডিমেড ল্যান্ডিং পেজ। পছন্দের ডিজাইন বেছে নিন, নিজের ব্র্যান্ডের রঙে সাজান, কোডিং ছাড়াই শুরু করুন।')">

    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="SellPata">
    <meta property="og:title" content="@yield('og_title', 'SellPata — ফেসবুকের ব্যবসার জন্য নিজের ব্র্যান্ডের ল্যান্ডিং পেজ')">
    <meta property="og:description" content="@yield('og_description', 'ফেসবুক উদ্যোক্তাদের জন্য রেডিমেড ল্যান্ডিং পেজ। পছন্দের ডিজাইন বেছে নিন, নিজের ব্র্যান্ডের রঙে সাজান।')">
    <meta property="og:locale" content="bn_BD">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="SellPata — নিজের ব্র্যান্ডের ল্যান্ডিং পেজ">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('og-image.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Hind Siliguri is the primary Bengali face; Noto Sans Bengali is the fallback. --}}
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>

    <script>
        window.__SELLPATA__ = {!! json_encode(\App\Support\SiteContent::bootstrap(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
    </script>

    <noscript>
        <div style="max-width:38rem;margin:4rem auto;padding:0 1.25rem;font-family:system-ui,sans-serif;line-height:1.8">
            <h1 style="font-size:1.6rem">SellPata</h1>
            <p>এই সাইটটি ব্যবহার করতে আপনার ব্রাউজারে জাভাস্ক্রিপ্ট চালু করুন। অথবা সরাসরি যোগাযোগ করুন।</p>
        </div>
    </noscript>
</body>
</html>
