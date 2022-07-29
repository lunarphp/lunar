<!DOCTYPE html>
<html lang="en"
      class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>

    <link rel="icon"
          type="image/png"
          href="https://cdn.getcandy.io/hub/favicon.svg">


    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css"
          rel="stylesheet">
    <link rel="preconnect"
          href="https://fonts.googleapis.com">
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;700;900&display=swap"
          rel="stylesheet">
    <link href="{{ asset('vendor/getcandy/admin-hub/app.css?v=1') }}"
          rel="stylesheet">

    <style>
        .filepond--credits {
            display: none !important;
        }
    </style>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script defer
            src="https://unpkg.com/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer
            src="https://unpkg.com/alpinejs@3.8.1/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.js"
            integrity="sha512-Fc8SDJVBwajCGX0A9z8lBeRPaCjR25Ek577z9PtQLB7CLBz7Mw1XhjbcD2yDWrGszL/uezKGidtGCng6Fhz3+A=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"></script>

    @livewireStyles
</head>

<body class="h-full overflow-hidden antialiased bg-gray-50 dark:bg-gray-900"
      :class="{ 'dark': darkMode }"
      x-data="{
          showExpandedMenu: $persist(false),
          showMobileMenu: false,
          darkMode: $persist(false),
      }">
    {!! \GetCandy\Hub\GetCandyHub::paymentIcons() !!}

    <div class="flex h-full">
        @include('adminhub::partials.navigation.side-menu-mobile')

        @include('adminhub::partials.navigation.side-menu')

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            @include('adminhub::partials.navigation.header-mobile')

            <main class="flex flex-1 overflow-hidden">
                <section class="flex-1 h-full min-w-0 overflow-y-auto lg:order-last">
                    @include('adminhub::partials.navigation.header')

                    <div class="px-4 py-8 mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
                        @yield('main', $slot)
                    </div>
                </section>

                @yield('menu')

                @if ($menu ?? false)
                    @include('adminhub::partials.navigation.side-menu-nested')
                @endif
            </main>
        </div>
    </div>

    <x-hub::notification />

    @livewire('hub-license')

    @livewireScripts

    <script src="{{ asset('vendor/getcandy/admin-hub/app.js') }}"></script>
</body>

</html>
