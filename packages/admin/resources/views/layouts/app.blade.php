<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>

    <link data-n-head="ssr" rel="icon" type="image/png" href="https://cdn.getcandy.io/hub/favicon.svg">
    <link rel="stylesheet" href="https://unpkg.com/trix@1.2.3/dist/trix.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
        rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/getcandy/admin-hub/app.css?v=1') }}" rel="stylesheet">

    <style>
        .filepond--credits {
            display: none !important;
        }

    </style>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.8.1/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.js"
        integrity="sha512-Fc8SDJVBwajCGX0A9z8lBeRPaCjR25Ek577z9PtQLB7CLBz7Mw1XhjbcD2yDWrGszL/uezKGidtGCng6Fhz3+A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        function updateTheme() {
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                    '(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        }

        function toggleTheme() {
            if (localStorage.theme === 'dark') localStorage.theme = 'light';
            else localStorage.theme = 'dark';
            updateTheme();
        }
        updateTheme();
    </script>
    @livewireStyles
</head>

<body>
    {!! \GetCandy\Hub\GetCandyHub::paymentIcons() !!}

    <div class="flex h-screen overflow-hidden bg-gray-100" x-data="{ showMenu: false }">

        @include('adminhub::layouts.navigation-menu')

        <div class="flex w-0 flex-1 flex-col overflow-hidden">
            <div class="relative z-10 flex h-16 flex-shrink-0 bg-white shadow md:hidden">
                <button @click="showMenu = true"
                    class="flex aspect-square items-center justify-center border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden">
                    <span class="sr-only">{{ __('adminhub::menu.open-sidebar') }}</span>
                    <!-- Heroicon name: outline/menu-alt-2 -->
                    <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </button>
            </div>
            <main class="relative flex-1 overflow-y-auto focus:outline-none">
                <div class="py-8">
                    @yield('main', $slot)
                </div>
            </main>
        </div>
    </div>
    <x-hub::notification />
    @livewire('hub-license')
    @livewireScripts
    <script src="{{ asset('vendor/getcandy/admin-hub/app.js') }}"></script>
</body>

</html>
