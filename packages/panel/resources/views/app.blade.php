<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title data-inertia>{{ config('lunar.panel.name', 'Lunar') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('vendor/lunar-panel/favicons/favicon.svg') }}" />
        <link rel="icon" type="image/x-icon" href="{{ asset('vendor/lunar-panel/favicons/favicon.ico') }}" sizes="any" />
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('vendor/lunar-panel/favicons/favicon-96x96.png') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vendor/lunar-panel/favicons/apple-touch-icon.png') }}" />
        <link rel="manifest" href="{{ asset('vendor/lunar-panel/favicons/site.webmanifest') }}" />
        <meta name="theme-color" content="#122036" />

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Geist+Mono:wght@400;500&display=swap"
            rel="stylesheet"
        />

        @php(app(\Illuminate\Foundation\Vite::class)->useHotFile(public_path('vendor/lunar-panel/build/hot')))
        @vite(['resources/css/app.css', 'resources/js/app.ts'], 'vendor/lunar-panel/build')
        @inertiaHead
    </head>
    <body class="font-sans text-[13px] leading-[1.45] antialiased [font-feature-settings:'cv11','ss01']">
        @inertia

        {{-- Clone per module: hot file and build directory must never leak between
             modules, or in from the panel's own hot file configured above. A module
             without a hot file gets its own conventional path, so another module's
             running dev server can never capture its tags. --}}
        @foreach (app(\Lunar\Panel\PanelManager::class)->registeredVites() as $name => $config)
            {!! (clone app(\Illuminate\Foundation\Vite::class))
                ->useHotFile($config['hotFile'] ?: public_path("vendor/lunar-panel/{$name}/hot"))
                ->useBuildDirectory($config['buildDirectory'])
                ->withEntryPoints((array) $config['input'])
                ->toHtml() !!}
        @endforeach
    </body>
</html>
