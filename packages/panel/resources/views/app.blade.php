<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title inertia>{{ config('lunar.panel.name', 'Lunar') }}</title>

        @foreach (app(\Lunar\Panel\PanelManager::class)->styles() as $name => $path)
            <link rel="stylesheet" href="{{ $path }}" />
        @endforeach

        @php(app(\Illuminate\Foundation\Vite::class)->useHotFile(public_path('vendor/lunar-panel/build/hot')))
        @vite(['resources/css/app.css', 'resources/js/app.ts'], 'vendor/lunar-panel/build')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
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
