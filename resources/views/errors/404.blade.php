<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found — SpeakVerse</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: system-ui, -apple-system, Segoe UI, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
        }
        .card {
            width: min(92vw, 440px);
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 36px 28px;
            text-align: center;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        }
        .badge {
            display: inline-block;
            font-size: 12px;
            letter-spacing: .08em;
            color: #0284c7;
            font-weight: 700;
            margin-bottom: 12px;
        }
        h1 { margin: 0 0 10px; font-size: 28px; }
        p { margin: 0 0 22px; color: #64748b; line-height: 1.6; }
        a {
            display: inline-block;
            background: linear-gradient(90deg, #06b6d4, #2563eb);
            color: #fff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">SPEAKVERSE</div>
        <h1>Page not found</h1>
        <p>The page you opened does not exist or has been moved.</p>
        <a href="{{ url('/') }}">Back to Home</a>
    </div>
</body>
</html>
