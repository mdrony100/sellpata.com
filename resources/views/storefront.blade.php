<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $bootstrap['page']['title'] }} — {{ $bootstrap['store']['name'] }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($bootstrap['content']['hero']['subline'] ?? ''), 150) }}">
    <link rel="canonical" href="{{ $bootstrap['page']['url'] }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app"></div>
<noscript><p>{{ $bootstrap['store']['name'] }} — {{ $bootstrap['page']['title'] }}</p></noscript>
<script>
    window.__STORE__ = {!! json_encode($bootstrap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
</script>
</body>
</html>
