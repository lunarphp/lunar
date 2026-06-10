@php
    /**
     * The self-contained checkout app's Inertia ROOT view (spec 0008 §A).
     * Lunar owns it end-to-end. The app's own prebuilt bundle and every
     * contributed chunk load SAME-ORIGIN from package routes, so a strict
     * `script-src 'self'` holds and install-and-go needs no vendor:publish.
     */
    $bundle = app(\Lunar\Checkout\Support\CheckoutBundle::class)->entry();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="lunar-checkout">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>

    @foreach ($bundle['css'] as $file)
        <link rel="stylesheet" href="{{ route('lunar.checkout.build', $file) }}">
    @endforeach

    @inertiaHead
</head>
<body>
    @if (empty($bundle['js']))
        {{-- dist/ not built yet. Install-and-go ships a prebuilt dist/; in local
             development of the package itself, run `npm install && npm run build`. --}}
        <p style="font-family: system-ui; padding: 2rem; color: #71717a;">
            Checkout assets not built. Run <code>npm run build</code> in
            <code>packages/checkout</code>.
        </p>
    @else
        @inertia

        {{-- Contributed element/gateway chunks (spec 0009). Each is a self-
             registering ES module that calls Lunar.registerCheckoutElement(...).
             Loaded after the app bundle so the shared runtime (window.Vue /
             window.Lunar) already exists; the registry is reactive, so a chunk
             that registers late re-renders its element in place. --}}
        <script type="module" src="{{ route('lunar.checkout.build', $bundle['js'][0]) }}"></script>

        @foreach (\Lunar\Checkout\Facades\CheckoutAssets::all() as $asset)
            <script type="module" src="{{ $asset['url'] }}" data-checkout-chunk="{{ $asset['package'] }}"></script>
        @endforeach
    @endif
</body>
</html>
