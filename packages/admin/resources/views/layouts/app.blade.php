<!DOCTYPE html>
<html lang="en"
      class="h-full">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0"/>

    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>

    <link rel="icon"
          type="image/png"
          href="https://cdn.getcandy.io/hub/favicon.svg">

    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;700;900&display=swap"
          rel="stylesheet">
    <link href="{{ asset('vendor/getcandy/admin-hub/app.css?v=1') }}"
          rel="stylesheet">

    <style>
        .filepond--credits {
            display: none !important;
        }

        .reduced #sidebar {
            width: 40px
        }

        #sidebar.w-20 .sidebar-cloak {
            display: none !important;
        }
    </style>

    <script defer
            src="https://unpkg.com/alpinejs@3.8.1/dist/cdn.min.js"></script>

    <script>
        function sideMenu() {
            return {
                init() { !('showExpandedMenu' in localStorage) ? localStorage.showExpandedMenu = false : null;this.toggleExpandedMenu(localStorage.showExpandedMenu) },
                toggleClass() { Boolean(localStorage.showExpandedMenu) && JSON.parse(localStorage.showExpandedMenu) ? document.documentElement.classList.add('expanded') : document.documentElement.classList.remove('expanded') },
                toggleExpandedMenu(state) { typeof state !== 'undefined' && (typeof state === 'boolean' || (state === 'true' || state === 'false')) ? localStorage.showExpandedMenu = JSON.parse(state) : localStorage.showExpandedMenu = !JSON.parse(localStorage.showExpandedMenu);this.toggleClass() },
            }
        };

        function darkMode() {
            return {
                init() { !('darkMode' in localStorage) ? localStorage.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches : null;this.toggleDarkMode(localStorage.darkMode) },
                toggleClass() { Boolean(localStorage.darkMode) && JSON.parse(localStorage.darkMode) ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark') },
                toggleDarkMode(state) { typeof state !== 'undefined' && (typeof state === 'boolean' || (state === 'true' || state === 'false')) ? localStorage.darkMode = JSON.parse(state) : localStorage.darkMode = !JSON.parse(localStorage.darkMode);this.toggleClass() }
            }
        }

        (function () {
            sideMenu().init();
            // To use when dark mode is ready
            // darkMode().init();
        })()
    </script>

    @livewireStyles
</head>

<body class="h-full overflow-hidden antialiased bg-gray-50 dark:bg-gray-900"
      x-data="{ showMobileMenu: false }">

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

    <x-hub::notification/>

    @livewire('hub-license')

    @livewireScripts

    <script src="{{ asset('vendor/getcandy/admin-hub/app.js') }}"></script>
</body>

</html>
