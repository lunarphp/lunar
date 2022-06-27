<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>

    <link data-n-head="ssr" rel="icon" type="image/png"  href="https://cdn.getcandy.io/hub/favicon.svg">
    <link rel="stylesheet" href="https://unpkg.com/trix@1.2.3/dist/trix.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.js" integrity="sha512-Fc8SDJVBwajCGX0A9z8lBeRPaCjR25Ek577z9PtQLB7CLBz7Mw1XhjbcD2yDWrGszL/uezKGidtGCng6Fhz3+A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @livewireStyles
  </head>
  <body class="antialiased">
    {!! \GetCandy\Hub\GetCandyHub::paymentIcons() !!}

    <div class="flex h-screen overflow-hidden bg-gray-100" x-data="{ showMenu: false }">
      <div class="fixed inset-0 z-40 flex md:hidden" role="dialog" aria-modal="true" x-cloak x-show="showMenu" x-transition>
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
              <span class="sr-only">{{ __('adminhub::menu.close-sidebar') }}</span>
              <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="flex items-center flex-shrink-0 px-4">
            <img class="w-auto h-8" src="https://getcandy.io/hub/getcandy_logo.svg" alt="GetCandy">
          </div>
          <div class="flex-1 h-0 mt-5 overflow-y-auto">
            <nav class="px-2 space-y-1">
              @livewire('sidebar')
            </nav>
          </div>
          <div class="flex flex-shrink-0 p-4 bg-white border-t border-gray-200">
            <a href="{{ route('hub.account') }}" class="flex-shrink-0 block group">
              <div class="flex items-center">
                <div>
                    @livewire('hub.components.avatar')
                </div>
                <div class="ml-3">
                  @livewire('hub.components.current-staff-name', [
                    'class' => 'text-sm font-medium text-gray-700 group-hover:text-gray-900 truncate w-32'
                  ])
                  <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                    {{ __('adminhub::account.view-profile') }}
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
        </div>
      </div>

      <div class="hidden md:flex md:flex-shrink-0">
        <div class="flex flex-col w-50">
          <div class="flex flex-col pt-5 pb-4 overflow-y-auto bg-white grow">
            <div class="flex items-center flex-shrink-0 px-4 mx-4 my-2">
              <img class="w-auto" src="https://getcandy.io/hub/getcandy_logo.svg" alt="GetCandy">
            </div>
            <div class="flex flex-col mt-5 grow">
              <nav class="flex-1 px-2 space-y-1 bg-white">

                @livewire('sidebar')

              </nav>
            </div>
          </div>
          <div class="flex flex-shrink-0 p-4 bg-white border-t border-gray-200">
            <a href="{{ route('hub.account') }}" class="flex-shrink-0 block group">
              <div class="flex items-center">
                <div>
                  @livewire('hub.components.avatar')
                </div>
                <div class="ml-3">
                  @livewire('hub.components.current-staff-name', [
                    'class' => 'text-sm font-medium text-gray-700 group-hover:text-gray-900 truncate w-32'
                  ])
                  <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                    {{ __('adminhub::account.view-profile') }}
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
            <span class="sr-only">{{ __('adminhub::menu.open-sidebar') }}</span>
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>
          <div class="flex justify-between flex-1 px-4">
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
    @livewire('hub-license')
    @livewireScripts
    <script src="{{ asset('vendor/getcandy/admin-hub/app.js') }}"></script>
  </body>
</html>
