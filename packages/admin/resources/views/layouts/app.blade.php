<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gray-50">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? 'Hub' }} | {{ config('app.name') }}</title>

    <link rel="icon"
          type="image/png"
          href="https://cdn.getcandy.io/hub/favicon.svg">
    <link rel="stylesheet"
          href="https://unpkg.com/trix@1.2.3/dist/trix.css" />
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css"
          rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
          rel="stylesheet">
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
            src="https://unpkg.com/alpinejs@3.8.1/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.js"
            integrity="sha512-Fc8SDJVBwajCGX0A9z8lBeRPaCjR25Ek577z9PtQLB7CLBz7Mw1XhjbcD2yDWrGszL/uezKGidtGCng6Fhz3+A=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"></script>
    @livewireStyles
</head>

<body class="antialiased">
    {!! \GetCandy\Hub\GetCandyHub::paymentIcons() !!}

    @livewireStyles
    </head>

    <body class="h-full overflow-hidden antialiased"
          x-data="{ showExpandedMenu: false, showInnerMenu: true }">
        {{-- {!! \GetCandy\Hub\GetCandyHub::paymentIcons() !!} --}}

        <div class="flex h-full">
            <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
            <div class="relative z-40 lg:hidden"
                 role="dialog"
                 aria-modal="true">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>

                <div class="fixed inset-0 z-40 flex">
                    <div class="relative flex flex-col flex-1 w-full max-w-xs bg-white focus:outline-none">
                        <div class="absolute top-0 right-0 pt-4 -mr-12">
                            <button type="button"
                                    class="flex items-center justify-center w-10 h-10 ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                                <span class="sr-only">Close sidebar</span>
                                <!-- Heroicon name: outline/x -->
                                <svg class="w-6 h-6 text-white"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="2"
                                     stroke="currentColor"
                                     aria-hidden="true">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="pt-5 pb-4">
                            <div class="flex items-center flex-shrink-0 px-4">
                                <img class="w-auto h-8"
                                     src="https://tailwindui.com/img/logos/workflow-mark.svg?color=indigo&shade=600"
                                     alt="Workflow">
                            </div>
                            <nav aria-label="Sidebar"
                                 class="mt-5">
                                <div class="px-2 space-y-1">
                                    <a href="#"
                                       class="flex items-center p-2 text-base font-medium text-gray-600 rounded-md group hover:bg-gray-50 hover:text-gray-900">
                                        <!-- Heroicon name: outline/home -->
                                        <svg class="w-6 h-6 mr-4 text-gray-400 group-hover:text-gray-500"
                                             xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="2"
                                             stroke="currentColor"
                                             aria-hidden="true">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Home
                                    </a>

                                    <a href="#"
                                       class="flex items-center p-2 text-base font-medium text-gray-600 rounded-md group hover:bg-gray-50 hover:text-gray-900">
                                        <!-- Heroicon name: outline/fire -->
                                        <svg class="w-6 h-6 mr-4 text-gray-400 group-hover:text-gray-500"
                                             xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="2"
                                             stroke="currentColor"
                                             aria-hidden="true">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                                        </svg>
                                        Trending
                                    </a>

                                    <a href="#"
                                       class="flex items-center p-2 text-base font-medium text-gray-600 rounded-md group hover:bg-gray-50 hover:text-gray-900">
                                        <!-- Heroicon name: outline/bookmark-alt -->
                                        <svg class="w-6 h-6 mr-4 text-gray-400 group-hover:text-gray-500"
                                             xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="2"
                                             stroke="currentColor"
                                             aria-hidden="true">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Bookmarks
                                    </a>

                                    <a href="#"
                                       class="flex items-center p-2 text-base font-medium text-gray-600 rounded-md group hover:bg-gray-50 hover:text-gray-900">
                                        <!-- Heroicon name: outline/inbox -->
                                        <svg class="w-6 h-6 mr-4 text-gray-400 group-hover:text-gray-500"
                                             xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="2"
                                             stroke="currentColor"
                                             aria-hidden="true">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        Messages
                                    </a>

                                    <a href="#"
                                       class="flex items-center p-2 text-base font-medium text-gray-600 rounded-md group hover:bg-gray-50 hover:text-gray-900">
                                        <!-- Heroicon name: outline/user -->
                                        <svg class="w-6 h-6 mr-4 text-gray-400 group-hover:text-gray-500"
                                             xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="2"
                                             stroke="currentColor"
                                             aria-hidden="true">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Profile
                                    </a>
                                </div>
                            </nav>
                        </div>
                        <div class="flex flex-shrink-0 p-4 border-t border-gray-200">
                            <a href="#"
                               class="flex-shrink-0 block group">
                                <div class="flex items-center">
                                    <div>
                                        <img class="inline-block w-10 h-10 rounded-full"
                                             src="https://images.unsplash.com/photo-1502685104226-ee32379fefbe?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                             alt="">
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-base font-medium text-gray-700 group-hover:text-gray-900">Emily
                                            Selman</p>
                                        <p class="text-sm font-medium text-gray-500 group-hover:text-gray-700">Account
                                            Settings</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="flex-shrink-0 w-14"
                         aria-hidden="true">
                        <!-- Force sidebar to shrink to fit close icon -->
                    </div>
                </div>
            </div>

            <!-- Static sidebar for desktop -->
            <div class="hidden lg:flex lg:flex-shrink-0">
                <div class="relative bg-white border-r border-gray-100"
                     :class="{ 'w-48': showExpandedMenu, 'w-20 items-center': !showExpandedMenu }">
                    <div>
                        <a href="{{ route('hub.index') }}"
                           class="flex items-center h-16">
                            <img src="https://markmead.dev/gc-logo.svg"
                                 alt="GetCandy Logo"
                                 class="w-auto h-10"
                                 x-show="showExpandedMenu" />

                            <img src="https://markmead.dev/gc-favicon.svg"
                                 alt="GetCandy Logo"
                                 class="w-8 h-8"
                                 x-show="!showExpandedMenu">
                        </a>

                        <div class="px-4 pt-4 border-t border-gray-100">
                            @livewire('sidebar')
                        </div>
                    </div>

                    <button x-on:click="showExpandedMenu = !showExpandedMenu"
                            class="absolute z-50 p-1 bg-white border border-gray-100 rounded -right-[13px] bottom-8">
                        <span :class="{ '-rotate-180': showExpandedMenu }"
                              class="block">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 20 20"
                                 fill="currentColor"
                                 class="w-4 h-4">
                                <path fill-rule="evenodd"
                                      d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                      clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>

                    {{-- <div class="sticky inset-x-0 bottom-0 z-10 bg-white">
                        <button x-on:click="showExpandedMenu = !showExpandedMenu"
                                class="absolute -right-[13px] block p-1 bg-white border border-gray-100 rounded bottom-20">
                            <span :class="{ '-rotate-180': showExpandedMenu }"
                                  class="block">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     class="w-4 h-4">
                                    <path fill-rule="evenodd"
                                          d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                          clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>

                        <div x-data="{ showUserMenu: false }"
                             x-on:mouseover="showUserMenu = true"
                             x-on:mouseleave="showUserMenu = false"
                             class="relative border-t border-gray-100">
                            <div x-show="showUserMenu"
                                 x-transition
                                 class="absolute p-2 -mb-2 bg-white border border-gray-100 rounded-lg bottom-full left-4 w-36">
                                <ul class="flex flex-col">
                                    <li>
                                        <a href="{{ route('hub.account') }}"
                                           class="block p-2 text-sm font-medium text-gray-500 rounded hover:bg-gray-50">
                                            {{ __('adminhub::account.view-profile') }}
                                        </a>
                                    </li>

                                    <li>
                                        <form method="POST"
                                              action="{{ route('hub.logout') }}">
                                            @csrf
                                            <button type="submit"
                                                    class="flex items-center w-full gap-2 p-2 text-gray-500 rounded hover:bg-gray-50">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-4 h-4"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor"
                                                     stroke-width="2">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>

                                                <span class="text-sm font-medium">Logout</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>

                            <div class="flex items-center h-16 gap-2 px-4 bg-white">
                                <div class="shrink-0">
                                    @livewire('hub.components.avatar', ['attributes' => ['class' => 'w-8 h-8 rounded-full']])
                                </div>

                                <div x-show="showExpandedMenu"
                                     class="leading-none">
                                    <strong>
                                        @livewire('hub.components.current-staff-name', [
                                            'class' => 'text-sm font-medium leading-none text-gray-900',
                                        ])
                                    </strong>

                                    <small class="block text-xs text-gray-500 leading-none mt-0.5">
                                        {{ __('adminhub::account.view-profile') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>

            <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
                <!-- Mobile top navigation -->
                <div class="lg:hidden">
                    <div class="flex items-center justify-between px-4 py-2 bg-indigo-600 sm:px-6 lg:px-8">
                        <div>
                            <img class="w-auto h-8"
                                 src="https://tailwindui.com/img/logos/workflow-mark.svg?color=white"
                                 alt="Workflow">
                        </div>
                        <div>
                            <button type="button"
                                    class="inline-flex items-center justify-center w-12 h-12 -mr-3 text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                                <span class="sr-only">Open sidebar</span>
                                <!-- Heroicon name: outline/menu -->
                                <svg class="w-6 h-6"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="2"
                                     stroke="currentColor"
                                     aria-hidden="true">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <main class="flex flex-1 overflow-hidden">
                    <section class="flex-1 h-full min-w-0 overflow-y-auto lg:order-last">
                        @include('adminhub::partials.header')

                        <div class="px-4 py-8 sm:px-6 lg:px-8">
                            @yield('main', $slot)
                        </div>
                    </section>

                    <!-- Secondary column (hidden on smaller screens) -->
                    @include('adminhub::partials.inner-menu')
                </main>
            </div>
        </div>


        {{-- <div class="flex h-screen overflow-hidden bg-gray-100" x-data="{ showMenu: false }">
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
    </div> --}}

        <x-hub::notification />

        @livewire('hub-license')

        @livewireScripts

        <script src="{{ asset('vendor/getcandy/admin-hub/app.js') }}"></script>
    </body>

</html>
