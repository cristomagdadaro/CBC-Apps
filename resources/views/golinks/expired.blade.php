<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Link Expired</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f8fafc; color: #1f2937; }
        .card { width: min(100%, 520px); background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 32px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); text-align: center; }
        h1 { margin: 0 0 12px; color: #991b1b; }
        p { margin: 0 0 12px; line-height: 1.6; }
        a { color: #166534; }
    </style>
</head>
<body>
<div class="card">
    <h1>Link Expired</h1>
    <p>This Go Link is no longer active.</p>
    <p><a href="{{ config('app.url') }}">Return to the main site</a></p>
</div>
</body>
</html>
