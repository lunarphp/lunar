<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>
    <link data-n-head="ssr" rel="icon" type="image/png"  href="{{ config('getcandy-hub.system.favicon', 'https://cdn.getcandy.io/hub/favicon.svg') }}">

    <link href="https://fonts.bunny.net/css2?family=Nunito&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/getcandy/admin-hub/app.css') }}" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>

    @livewireStyles
  </head>
  <body>

    {{ $slot }}

    <script></script>

    @livewireScripts
  </body>
</html>
