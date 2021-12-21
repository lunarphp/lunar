<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>

    <link data-n-head="ssr" rel="icon" type="image/png" sizes="16x16" href="https://getcandy.io/favicon-16x16.png">
    <link data-n-head="ssr" rel="icon" type="image/png" sizes="32x32" href="https://getcandy.io/favicon-32x32.png">
    <link rel="stylesheet" href="https://unpkg.com/trix@1.2.3/dist/trix.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/getcandy/admin-hub/app.css') }}" rel="stylesheet">

    <style>
    .filepond--credits {
        display:none!important;
      }
    </style>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @livewireStyles
  </head>
  <body>

    <!--
      This example requires Tailwind CSS v2.0+

      This example requires some changes to your config:

      ```
      // tailwind.config.js
      module.exports = {
        // ...
        plugins: [
          // ...
          require('@tailwindcss/forms'),
        ]
      }
      ```
    -->
    <div class="flex h-screen overflow-hidden bg-gray-100" x-data="{ showMenu: false }">
      <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
      <div class="fixed inset-0 z-40 flex md:hidden" role="dialog" aria-modal="true" x-cloak x-show="showMenu" x-transition>
        <!--
          Off-canvas menu overlay, show/hide based on off-canvas menu state.

          Entering: "transition-opacity ease-linear duration-300"
            From: "opacity-0"
            To: "opacity-100"
          Leaving: "transition-opacity ease-linear duration-300"
            From: "opacity-100"
            To: "opacity-0"
        -->
        <div
          class="fixed inset-0 bg-gray-600 bg-opacity-75"
          x-transition:enter="transition-opacity ease-linear duration-300"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:enter="transition-opacity ease-linear duration-300"
          x-transition:enter-start="opacity-100"
          x-transition:enter-end="opacity-0"
          x-show="showMenu"
          aria-hidden="true"
        ></div>

        <!--
          Off-canvas menu, show/hide based on off-canvas menu state.

          Entering: "transition ease-in-out duration-300"
            From: "-translate-x-full"
            To: "translate-x-0"
          Leaving: "transition ease-in-out duration-300"
            From: "translate-x-0"
            To: "-translate-x-full"
        -->
        <div
          class="relative flex flex-col flex-1 w-full max-w-xs pt-5 pb-4 bg-white"
          x-transition:enter="transition ease-in-out duration-300"
          x-transition:enter-start="-translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transition ease-in-out duration-300"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="-translate-x-full"
          x-show="showMenu"
        >
          <!--
            Close button, show/hide based on off-canvas menu state.

            Entering: "ease-in-out duration-300"
              From: "opacity-0"
              To: "opacity-100"
            Leaving: "ease-in-out duration-300"
              From: "opacity-100"
              To: "opacity-0"
          -->
          <div
            class="absolute top-0 right-0 pt-2 -mr-12"
            x-transition:enter="ease-in-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-show="showMenu"
          >
            <button @click="showMenu = false" class="flex items-center justify-center w-10 h-10 ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
              <span class="sr-only">Close sidebar</span>
              <!-- Heroicon name: outline/x -->
              <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="flex items-center flex-shrink-0 px-4">
            <img class="w-auto h-8" src="https://getcandy.io/getcandy_logo.svg" alt="Workflow">
          </div>
          <div class="flex-1 h-0 mt-5 overflow-y-auto">
            <nav class="px-2 space-y-1">

              @livewire('sidebar')

            </nav>
          </div>
          <div class="flex-shrink-0 flex border-t border-gray-200 bg-white p-4">
            <a href="#" class="flex-shrink-0 group block">
              <div class="flex items-center">
                <div>
                  <img class="inline-block h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1517365830460-955ce3ccd263?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80" alt="">
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                    Whitney Francis
                  </p>
                  <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                    View profile
                  </p>
                </div>
                <div class="pl-2">
                  <form method="POST" action="{{ route('hub.logout') }}">
                    @csrf
                    <button>
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 11V13L17 13V16L22 12L17 8V11L8 11Z" fill="#9A9AA9"/>
                        <path d="M4 21H13C14.103 21 15 20.103 15 19V15H13V19H4L4 5H13V9H15V5C15 3.897 14.103 3 13 3H4C2.897 3 2 3.897 2 5L2 19C2 20.103 2.897 21 4 21Z" fill="#9A9AA9"/>
                      </svg>
                    </button>
                  </form>
                </div>
              </div>
            </a>
          </div>
        </div>
        <div class="flex-shrink-0 w-14" aria-hidden="true">
          <!-- Dummy element to force sidebar to shrink to fit close icon -->
        </div>
      </div>

      <!-- Static sidebar for desktop -->
      <div class="hidden md:flex md:flex-shrink-0">
        <div class="flex flex-col w-50">
          <!-- Sidebar component, swap this element with another sidebar if you like -->
          <div class="flex flex-col grow pt-5 pb-4 overflow-y-auto bg-white">
            <div class="flex items-center flex-shrink-0 px-4 mx-4 my-2">
              <img class="w-auto" src="https://getcandy.io/getcandy_logo.svg" alt="Workflow">
            </div>
            <div class="flex flex-col grow mt-5">
              <nav class="flex-1 px-2 space-y-1 bg-white">

                @livewire('sidebar')

              </nav>
            </div>
          </div>
          <div class="flex-shrink-0 flex border-t border-gray-200 bg-white p-4">
            <a href="#" class="flex-shrink-0 group block">
              <div class="flex items-center">
                <div>
                  <img class="inline-block h-10 w-10 rounded-full" src="{{ Auth::user()->gravatar }}" alt="">
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                    {{ Auth::user()->full_name }}
                  </p>
                  <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                    View profile
                  </p>
                </div>
                <div class="pl-2">
                  <form method="POST" action="{{ route('hub.logout') }}">
                    @csrf
                    <button>
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 11V13L17 13V16L22 12L17 8V11L8 11Z" fill="#9A9AA9"/>
                        <path d="M4 21H13C14.103 21 15 20.103 15 19V15H13V19H4L4 5H13V9H15V5C15 3.897 14.103 3 13 3H4C2.897 3 2 3.897 2 5L2 19C2 20.103 2.897 21 4 21Z" fill="#9A9AA9"/>
                      </svg>
                    </button>
                  </form>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
      <div class="flex flex-col flex-1 w-0 overflow-hidden">
        <div class="relative z-10 flex flex-shrink-0 h-16 bg-white shadow md:hidden">
          <button @click="showMenu = true" class="px-4 text-gray-500 border-r border-gray-200 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden">
            <span class="sr-only">Open sidebar</span>
            <!-- Heroicon name: outline/menu-alt-2 -->
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>
          <div class="flex justify-between flex-1 px-4">
{{--
            <div class="flex flex-1">
              <form class="flex w-full md:ml-0" action="#" method="GET">
                <label for="search-field" class="sr-only">Search</label>
                <div class="relative w-full text-gray-400 focus-within:text-gray-600">
                  <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                    <!-- Heroicon name: solid/search -->
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <input id="search-field" class="block w-full h-full py-2 pl-8 pr-3 text-gray-900 placeholder-gray-500 border-transparent focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent sm:text-sm" placeholder="Search" type="search" name="search">
                </div>
              </form>
            </div>
            <div class="flex flex-1 items-center ml-4 md:ml-6">
              <button class="p-1 text-gray-400 bg-white rounded-full hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span class="sr-only">View notifications</span>
                <!-- Heroicon name: outline/bell -->
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </button>

              <!-- Profile dropdown -->
              <div class="relative ml-3">
                <div>
                  <button type="button" class="flex items-center max-w-xs text-sm bg-white rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                    <span class="sr-only">Open user menu</span>
                    <x-hub::gravatar email="{{ Auth::user()->email }}" class="w-8 h-8 rounded-full" />
                  </button>
                </div>

                <!--
                  Dropdown menu, show/hide based on menu state.

                  Entering: "ease-out duration-100"
                    From: "opacity-0 scale-95"
                    To: "opacity-100 scale-100"
                  Leaving: "ease-in duration-75"
                    From: "opacity-100 scale-100"
                    To: "opacity-0 scale-95"
                -->
                <div class="absolute right-0 w-48 py-1 mt-2 origin-top-right scale-95 bg-white rounded-md shadow-lg opacity-0 ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                  <!-- Active: "bg-gray-100", Not Active: "" -->
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-0">Your Profile</a>

                  <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-1">Settings</a>

                  <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-2">Sign out</a>
                </div>
              </div>
            </div>
--}}
          </div>
        </div>
        <main class="relative flex-1 overflow-y-auto focus:outline-none">
          <div class="py-8">
            @yield('main', $slot)
          </div>
        </main>
      </div>
    </div>
    <x-hub::notification />
    @livewire('livewire-ui-modal')
    @livewire('hub-license')
    @livewireScripts
    <script src="{{ asset('vendor/getcandy/admin-hub/app.js') }}"></script>
  </body>
</html>
