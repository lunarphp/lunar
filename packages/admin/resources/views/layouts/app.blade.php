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

    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;700;900&display=swap"
          rel="stylesheet">
    <link href="{{ asset('vendor/getcandy/admin-hub/app.css?v=1') }}"
          rel="stylesheet">

    @if ($styles = \GetCandy\Hub\GetCandyHub::styles())
        <!-- Package Styles -->
        @foreach ($styles as $asset)
            <link href="{!! $asset->url() !!}"
                  rel="stylesheet">
        @endforeach
    @endif


    <style>
        .filepond--credits {
            display: none !important;
        }
    </style>

    <script defer
            src="https://unpkg.com/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>

    <script defer
            src="https://unpkg.com/alpinejs@3.8.1/dist/cdn.min.js"></script>

    <script>
        JSON.parse(localStorage.getItem('_x_showExpandedMenu')) ?
            document.documentElement.classList.add('app-sidemenu-expanded') :
            document.documentElement.classList.remove('app-sidemenu-expanded');

        document.addEventListener('alpine:init', () => {
            document.documentElement.classList.remove('app-sidemenu-expanded');
        })
    </script>

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

    @if ($scripts = \GetCandy\Hub\GetCandyHub::scripts())
        <!-- Package Scripts -->
        @foreach ($scripts as $asset)
            <script src="{!! $asset->url() !!}"></script>
        @endforeach
    @endif

    <script src="{{ asset('vendor/getcandy/admin-hub/app.js') }}"></script>
</body>

</html>
