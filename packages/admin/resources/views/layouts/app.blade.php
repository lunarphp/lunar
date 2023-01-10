<!DOCTYPE html>
<html lang="en"
      class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>

    <x-hub::branding.favicon />

    <script defer
          src="https://scaleflex.cloudimg.io/v7/plugins/filerobot-image-editor/latest/filerobot-image-editor.min.js"></script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:100,200,300,400,500,600,700,800,900" rel="stylesheet" />

    @livewireTableStyles

    <link href="{{ asset('vendor/lunar/admin-hub/app.css?v=1') }}"
          rel="stylesheet">

    @if ($styles = \Lunar\Hub\LunarHub::styles())
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
            src="https://unpkg.com/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>

    <script defer
            src="https://unpkg.com/alpinejs@3.8.1/dist/cdn.min.js"></script>

    <script>
        JSON.parse(localStorage.getItem('_x_menuCollapsed')) ?
            document.documentElement.classList.add('app-sidemenu-expanded') :
            document.documentElement.classList.remove('app-sidemenu-expanded');

        document.addEventListener('alpine:init', () => {
            document.documentElement.classList.remove('app-sidemenu-expanded');
        })
    </script>

    @livewireStyles
</head>

<body class="antialiased bg-gray-100 dark:bg-gray-900"
      x-data="{
          menuCollapsed: true,
          showMobileMenu: false,
      }">
    {!! \Lunar\Hub\LunarHub::paymentIcons() !!}

    <div>
        <div>
            @include('adminhub::partials.navigation.header')

            <div
                class="bg-gray-800 fixed inset-0 top-16 w-64"
                x-cloak
            >
                <x-hub::menus.app-side />
            </div>

            <div class="pl-64">

                <main class="flex flex-1 overflow-hidden">
                    <section class="flex-1 h-full min-w-0 overflow-y-auto lg:order-last">
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

        {{-- @include('adminhub::partials.navigation.side-menu-mobile') --}}

        {{-- @include('adminhub::partials.navigation.side-menu') --}}

        {{-- <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
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
        </div> --}}
    </div>

    <x-hub::notification />

    @livewire('hub-license')

    @livewireScripts

    @if ($scripts = \Lunar\Hub\LunarHub::scripts())
        @foreach ($scripts as $asset)
            <script src="{!! $asset->url() !!}"></script>
        @endforeach
    @endif

    <script src="{{ asset('vendor/lunar/admin-hub/app.js') }}"></script>
</body>

</html>
