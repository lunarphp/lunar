<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title inertia>{{ config('lunar.panel.name', 'Lunar') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Geist+Mono:wght@400;500&display=swap"
            rel="stylesheet"
        />

        @foreach (app(\Lunar\Panel\PanelManager::class)->styles() as $name => $path)
            <link rel="stylesheet" href="{{ $path }}" />
        @endforeach

        @php(app(\Illuminate\Foundation\Vite::class)->useHotFile(public_path('vendor/lunar-panel/build/hot')))
        @vite(['resources/css/app.css', 'resources/js/app.ts'], 'vendor/lunar-panel/build')
        @inertiaHead
    </head>
    <body class="font-sans text-[13px] leading-[1.45] antialiased [font-feature-settings:'cv11','ss01']">
        @inertia

        @foreach (app(\Lunar\Panel\PanelManager::class)->scripts() as $name => $path)
            <script src="{{ $path }}" defer></script>
        @endforeach

        @foreach (app(\Lunar\Panel\PanelManager::class)->registeredVites() as $name => $config)
            @php($config['hotFile'] && app(\Illuminate\Foundation\Vite::class)->useHotFile($config['hotFile']))
            {!! app(\Illuminate\Foundation\Vite::class)
                ->useBuildDirectory($config['buildDirectory'])
                ->withEntryPoints((array) $config['input'])
                ->toHtml() !!}
        @endforeach
    </body>
</html>
