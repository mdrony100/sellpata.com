<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'অ্যাডমিন') — SellPata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --brand:#0d3b2e; --brand-2:#155e47; --lime:#c9f24d; --cream:#faf7ef;
            --ink:#1b2320; --muted:#5d6763; --line:#e4ddcd; --card:#fff;
            --danger:#b3261e; --ok:#155e47;
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:'Hind Siliguri','Noto Sans Bengali',system-ui,sans-serif;background:var(--cream);color:var(--ink);font-size:16px;line-height:1.7}
        a{color:var(--brand-2)}
        header.topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;background:var(--brand);color:#f7f5ee;padding:12px 20px}
        header.topbar a{color:#f7f5ee;text-decoration:none}
        .topbar__brand{font-weight:700;font-size:1.15rem;letter-spacing:.2px}
        .topbar__brand span{color:var(--lime)}
        .topbar__actions{display:flex;align-items:center;gap:14px;font-size:.95rem}
        .shell{display:grid;grid-template-columns:250px 1fr;min-height:calc(100vh - 58px)}
        nav.sidebar{background:#fff;border-right:1px solid var(--line);padding:18px 12px}
        nav.sidebar a{display:block;padding:9px 14px;border-radius:10px;text-decoration:none;color:var(--ink);font-size:.98rem}
        nav.sidebar a:hover{background:#f2eee2}
        nav.sidebar a.active{background:var(--brand);color:#fff}
        nav.sidebar .group{margin:16px 0 6px;padding:0 14px;font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)}
        main.content{padding:24px clamp(16px,3vw,36px) 60px;max-width:1180px}
        h1{font-size:1.6rem;margin:0 0 4px}
        .lede{color:var(--muted);margin:0 0 22px}
        .grid{display:grid;gap:18px;grid-template-columns:minmax(0,1fr) 320px}
        @media (max-width:960px){.shell{grid-template-columns:1fr}nav.sidebar{border-right:0;border-bottom:1px solid var(--line);display:flex;flex-wrap:wrap;gap:6px}nav.sidebar .group{width:100%}.grid{grid-template-columns:1fr}}
        .card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:18px}
        .card h2{font-size:1.05rem;margin:0 0 12px}
        main{min-width:0}
        .grid>*{min-width:0}
        /* Wide tables scroll inside their card instead of pushing the page sideways. */
        .card{overflow-x:auto}
        table{width:100%;border-collapse:collapse;font-size:.95rem}
        th,td{text-align:right;padding:10px 8px;border-bottom:1px solid var(--line);vertical-align:middle}
        th{font-weight:600;color:var(--muted);font-size:.85rem}
        td form{display:inline}
        label{display:block;font-size:.9rem;font-weight:500;margin:12px 0 6px}
        input[type=text],input[type=email],input[type=url],input[type=number],input[type=password],select,textarea{
            width:100%;padding:10px 12px;border:1px solid #cfc7b4;border-radius:10px;font:inherit;background:#fff;color:var(--ink)
        }
        textarea{min-height:84px;resize:vertical}
        input:focus,select:focus,textarea:focus{outline:2px solid var(--brand-2);outline-offset:1px}
        .row{display:grid;gap:12px;grid-template-columns:1fr 1fr}
        @media (max-width:620px){.row{grid-template-columns:1fr}}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:999px;border:1px solid transparent;background:var(--brand);color:#fff;font:inherit;font-weight:600;cursor:pointer;text-decoration:none}
        .btn:hover{background:var(--brand-2)}
        .btn--ghost{background:transparent;color:var(--brand);border-color:#bfd8cb}
        .btn--danger{background:transparent;color:var(--danger);border-color:#e8c3bf;padding:6px 12px;font-weight:500}
        .btn--sm{padding:6px 12px;font-size:.9rem;font-weight:500}
        .check{display:flex;align-items:center;gap:8px;margin-top:14px;font-size:.95rem}
        .flash{background:#e7f6ec;border:1px solid #b7dfc5;color:#14532d;padding:10px 14px;border-radius:12px;margin-bottom:16px}
        .errors{background:#fdecea;border:1px solid #f3c7c1;color:#8a1c12;padding:10px 14px;border-radius:12px;margin-bottom:16px}
        .errors ul{margin:6px 0 0;padding-inline-start:18px}
        .muted{color:var(--muted)}
        .pill{display:inline-block;padding:2px 10px;border-radius:999px;font-size:.78rem;background:#eef2ec;color:var(--muted)}
        .pill--on{background:#e3f4e7;color:#14532d}
        .stat{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px}
        .stat .card{padding:16px}
        .stat strong{display:block;font-size:1.7rem}
        .swatches{display:flex;gap:8px;margin-top:6px}
        .swatch{width:100%;height:34px;border-radius:8px;border:1px solid var(--line)}
        .swatch label{font-size:.72rem;margin:2px 0 0;color:var(--muted);font-weight:400}
        .msg{border:1px solid var(--line);border-radius:12px;padding:12px 14px;margin-bottom:12px;background:#fff}
        .msg--new{border-color:#b7dfc5;background:#f6fdf8}
    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar__brand">Sell<span>Pata</span> অ্যাডমিন</div>
    <div class="topbar__actions">
        <a href="{{ route('home') }}" target="_blank" rel="noopener">সাইট দেখুন ↗</a>
        <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button class="btn btn--sm btn--ghost" type="submit">লগ আউট</button>
        </form>
    </div>
</header>
<div class="shell">
    <nav class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">ড্যাশবোর্ড</a>
        <div class="group">কনটেন্ট</div>
        <a href="{{ route('admin.templates.index') }}" class="{{ request()->routeIs('admin.templates.*') ? 'active' : '' }}">টেমপ্লেট</a>
        <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">ক্যাটাগরি</a>
        <a href="{{ route('admin.benefits.index') }}" class="{{ request()->routeIs('admin.benefits.*') ? 'active' : '' }}">সুবিধা</a>
        <a href="{{ route('admin.steps.index') }}" class="{{ request()->routeIs('admin.steps.*') ? 'active' : '' }}">ধাপ</a>
        <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">ক্লায়েন্ট</a>
        <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">রিভিউ</a>
        <div class="group">অন্যান্য</div>
        <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">বার্তা</a>
        <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">সেটিংস</a>
    </nav>
    <main class="content">
        @if (session('status'))
            <div class="flash" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors" role="alert">
                ফর্মে কিছু তথ্য ঠিক নেই:
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
