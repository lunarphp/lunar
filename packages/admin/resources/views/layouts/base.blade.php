<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>
    <link data-n-head="ssr" rel="icon" type="image/png"  href="https://cdn.getcandy.io/hub/favicon.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
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
