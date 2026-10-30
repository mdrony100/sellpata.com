<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>অ্যাডমিন লগইন — SellPata</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0d3b2e;font-family:'Hind Siliguri',system-ui,sans-serif;color:#1b2320}
        .card{background:#faf7ef;border-radius:20px;padding:32px;width:min(420px,92vw);box-shadow:0 24px 60px rgba(0,0,0,.25)}
        h1{margin:0 0 6px;font-size:1.5rem}
        p.lede{margin:0 0 20px;color:#5d6763;font-size:.95rem}
        label{display:block;margin:14px 0 6px;font-weight:500;font-size:.92rem}
        input{width:100%;padding:11px 13px;border:1px solid #cfc7b4;border-radius:10px;font:inherit;background:#fff}
        input:focus{outline:2px solid #155e47;outline-offset:1px}
        button{width:100%;margin-top:20px;padding:12px;border:0;border-radius:999px;background:#0d3b2e;color:#fff;font:inherit;font-weight:600;cursor:pointer}
        button:hover{background:#155e47}
        .err{background:#fdecea;border:1px solid #f3c7c1;color:#8a1c12;padding:9px 12px;border-radius:10px;font-size:.9rem;margin-top:14px}
        a{color:#155e47;font-size:.9rem}
    </style>
</head>
<body>
<div class="card">
    <h1>SellPata অ্যাডমিন</h1>
    <p class="lede">ওয়েবসাইটের কনটেন্ট পরিচালনা করতে লগইন করুন।</p>
    <form method="post" action="{{ route('admin.login.attempt') }}">
        @csrf
        <label for="email">ইমেইল</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        <label for="password">পাসওয়ার্ড</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        @error('email')
            <div class="err">{{ $message }}</div>
        @enderror
        <button type="submit">লগইন করুন</button>
    </form>
    <p style="margin:16px 0 0"><a href="{{ route('home') }}">← ওয়েবসাইটে ফিরে যান</a></p>
</div>
</body>
</html>
