<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout — Lunar</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f4f4f5;
            color: #18181b;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 3rem 1rem;
        }
        .checkout {
            width: 100%;
            max-width: 720px;
        }
        .checkout__header {
            margin-bottom: 2rem;
        }
        .checkout__eyebrow {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #71717a;
            margin: 0 0 0.25rem;
        }
        .checkout__title {
            font-size: 1.75rem;
            margin: 0;
        }
        .checkout__region {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .checkout__region h2 {
            font-size: 1rem;
            margin: 0 0 0.75rem;
        }
        .checkout__placeholder {
            border: 1px dashed #d4d4d8;
            border-radius: 0.5rem;
            padding: 1.25rem;
            color: #71717a;
            font-size: 0.875rem;
        }
        .checkout__note {
            margin-top: 2rem;
            font-size: 0.8125rem;
            color: #a1a1aa;
            text-align: center;
        }
        code { font-family: ui-monospace, SFMono-Regular, monospace; }
    </style>
</head>
<body>
    <main class="checkout">
        <header class="checkout__header">
            <p class="checkout__eyebrow">Lunar Checkout</p>
            <h1 class="checkout__title">Checkout</h1>
        </header>

        <section class="checkout__region">
            <h2>Main</h2>
            <div class="checkout__placeholder">
                Checkout elements (contact, addresses, shipping, payment) render here.
            </div>
        </section>

        <section class="checkout__region">
            <h2>Summary</h2>
            <div class="checkout__placeholder">
                Order summary &amp; discount render here.
            </div>
        </section>

        <p class="checkout__note">
            Placeholder view from <code>lunarphp/checkout</code>. Mount point for
            <code>&lt;LunarCheckout/&gt;</code> — element model lands in later specs.
        </p>
    </main>
</body>
</html>
