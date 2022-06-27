<div class="lg:hidden">
    <div class="flex items-center justify-between h-16 px-4 bg-white border-b border-gray-100 sm:px-6 lg:px-8">
        <a href="{{ route('hub.index') }}"
           class="block">
            <img class="w-8 h-8"
                 src="https://markmead.dev/gc-favicon.svg"
                 alt="GetCandy Logo">
        </a>

        <div class="flex items-center gap-4">
            <button type="button"
                    x-on:click="showMobileMenu = true">
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

            <span class="w-px h-8 bg-gray-100"
                  aria-hidden="true"></span>

            <div x-data="{ showUserMenu: false }"
                 x-on:click="showUserMenu = !showUserMenu"
                 x-on:click.away="showUserMenu = false"
                 class="relative">
                <div x-show="showUserMenu"
                     x-transition
                     class="absolute z-50 p-2 -mt-2 bg-white border border-gray-100 rounded-lg top-full right-4 w-36">
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

                                    <span class="text-sm font-medium">
                                        {{ __('adminhub::account.logout') }}
                                    </span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

                <div class="flex items-center h-16 gap-2">
                    <div class="shrink-0">
                        @livewire('hub.components.avatar')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
