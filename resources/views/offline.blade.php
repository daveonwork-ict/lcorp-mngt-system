<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>RC Store RMS - Offline</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --brand: #0b5ed7;
            --brand-alt: #0f766e;
            --text: #1f2937;
            --muted: #6b7280;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at 20% 20%, rgba(11, 94, 215, 0.12), transparent 38%), var(--bg);
            color: var(--text);
            display: grid;
            place-items: center;
            padding: 1rem;
        }

        .offline-card {
            width: min(560px, 100%);
            background: var(--card);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 14px 40px rgba(2, 6, 23, 0.1);
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 0.85rem;
        }

        .badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand), var(--brand-alt));
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        h1 {
            margin: 0 0 0.65rem;
            font-size: clamp(1.15rem, 3vw, 1.6rem);
        }

        p {
            margin: 0 0 0.85rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .actions {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        button,
        a {
            min-height: 44px;
            border-radius: 10px;
            border: none;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-secondary {
            background: #eef2ff;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <main class="offline-card" aria-live="polite">
        <div class="brand"><span class="badge">R</span> RC Store RMS</div>
        <h1>You are offline</h1>
        <p>You are currently offline. Please reconnect to continue using RC Store RMS.</p>
        <p>Recent safe app assets may still be available, but transactional pages are intentionally not cached for security.</p>
        <div class="actions">
            <button class="btn-primary" type="button" onclick="window.location.reload()">Retry Connection</button>
            <a class="btn-secondary" href="{{ url('/dashboard/owner') }}">Return to Dashboard</a>
        </div>
    </main>
</body>
</html>
