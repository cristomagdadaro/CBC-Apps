<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $goLink->og_title ?: 'Redirecting' }}</title>
    <meta property="og:title" content="{{ $goLink->og_title ?: 'Redirecting' }}">
    <meta property="og:description" content="{{ $goLink->og_description ?: 'Redirecting to a verified resource.' }}">
    <meta property="og:image" content="{{ $goLink->og_image ?: asset('img/logo.png') }}">
    <meta property="og:url" content="{{ $goLink->public_url }}">
    <meta property="og:type" content="website">
    <meta http-equiv="refresh" content="1;url={{ $goLink->target_url }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f4f7f4; color: #1f2937; }
        .card { width: min(100%, 560px); background: #fff; border: 1px solid #d1d5db; border-radius: 16px; padding: 32px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); text-align: center; }
        h1 { margin: 0 0 12px; font-size: 24px; }
        p { margin: 0 0 12px; line-height: 1.6; }
        a { color: #166534; }
    </style>
</head>
<body>
<div class="card">
    <h1>Redirecting</h1>
    <p>You are being redirected to the requested destination.</p>
    <p>If it does not continue automatically, <a href="{{ $goLink->target_url }}">open the target link</a>.</p>
    <p><small>{{ number_format((int) $goLink->clicks) }} visits</small></p>
</div>
</body>
</html>
