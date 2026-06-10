@php
    /**
     * The self-contained checkout app's Inertia ROOT view (spec 0008 §A).
     * Lunar owns it end-to-end. The app's prebuilt bundle and every contributed
     * element/gateway chunk (spec 0009) are rendered with Laravel's own Vite
     * class — one mechanism, dev hot file vs build manifest handled for us,
     * exactly like a Statamic addon.
     */
    use Illuminate\Support\Facades\Vite;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="lunar-checkout">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>

    @inertiaHead

    {{-- The checkout app itself. Dev (package `npm run dev`) → its hot file in
         the symlinked vendor dir; otherwise the published build in public/
         (tag lunar-checkout-assets). Laravel's Vite class makes the switch. --}}
    {{
        Vite::useHotFile(\Lunar\Checkout\CheckoutServiceProvider::appHotFile())
            ->useBuildDirectory(\Lunar\Checkout\CheckoutServiceProvider::appBuildDirectory())
            ->withEntryPoints(['resources/js/app.js'])
    }}

    {{-- Contributed element/gateway chunks (spec 0009). Each is a self-
         registering ES module that calls Lunar.registerCheckoutElement(...); the
         shared runtime (window.Vue / window.Lunar) already exists by the time it
         runs, and the registry is reactive, so a chunk that registers late
         re-renders its element in place. --}}
    @foreach (\Lunar\Checkout\Facades\CheckoutAssets::all() as $vite)
        {{
            Vite::useHotFile($vite['hotFile'])
                ->useBuildDirectory($vite['buildDirectory'])
                ->withEntryPoints($vite['input'])
        }}
    @endforeach
</head>
<body>
    @inertia
</body>
</html>
