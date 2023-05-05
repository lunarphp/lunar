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
        src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>

    <script defer
        src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>

    <script defer
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.8.1/dist/cdn.min.js"></script>

    @livewireStyles
</head>

<body class="antialiased bg-gray-100 dark:bg-gray-900"
      x-data="{
          menuCollapsed: $persist(false),
          showMobileMenu: false,
          toggleMenu () {
            this.menuCollapsed = !this.menuCollapsed
            this.showMobileMenu = !this.showMobileMenu
          }
      }"
    >
    {!! \Lunar\Hub\LunarHub::paymentIcons() !!}

    <div>
        <div>
            @include('adminhub::partials.navigation.header')

            <div
                :class="{
                    'bg-gray-800 fixed inset-0 z-50 md:z-auto top-[56px] w-64 transition-all ease-in-out': true,
                    '-ml-64 md:ml-0': !showMobileMenu,
                    'md:-ml-64': menuCollapsed
                }"
                x-cloak
            >
                <x-hub::menus.app-side />
            </div>

            <div class="transition-all ease-in-out" :class="{
                'md:pl-64': !menuCollapsed
            }" x-cloak>

                <main class="flex flex-1 mt-12">
                    <section class="flex-1 h-full min-w-0 lg:order-last">
                        <div class="px-4 py-8 mx-auto max-w-screen-7xl sm:px-6 lg:px-6">
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
