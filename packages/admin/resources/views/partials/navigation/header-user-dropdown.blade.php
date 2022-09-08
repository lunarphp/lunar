<div class="flex items-center justify-end gap-4">
    <button x-on:click="darkMode = !darkMode"
            class="p-2 text-gray-700 transition rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-200">
        <span x-show="darkMode"
              x-cloak>
            <svg xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke-width="1.5"
                 stroke="currentColor"
                 class="w-4 h-4">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
        </span>

        <span x-show="!darkMode"
              x-cloak>
            <svg xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke-width="1.5"
                 stroke="currentColor"
                 class="w-4 h-4">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
            </svg>

        </span>
    </button>

    <div x-data="{ showUserMenu: false }"
         x-on:mouseover="showUserMenu = true"
         x-on:mouseleave="showUserMenu = false"
         class="relative">
        <div x-cloak
             x-transition
             x-show="showUserMenu"
             class="absolute z-50 p-2 -mt-2 bg-white border border-gray-100 rounded-lg dark:bg-gray-900 dark:border-gray-800 top-full right-4 w-36">
            <ul>
                <li>
                    <a href="{{ route('hub.account') }}"
                       class="menu-link menu-link--inactive">
                        <span class="text-sm font-medium">
                            {{ __('adminhub::account.view-profile') }}
                        </span>
                    </a>
                </li>

                <li>
                    <form method="POST"
                          action="{{ route('hub.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full menu-link menu-link--inactive">
                            <x-hub::icon ref="logout"
                                         class="w-4 h-4 shrink-0" />

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

            <div class="hidden leading-none sm:block">
                <strong>
                    @livewire('hub.components.current-staff-name', [
                        'class' => 'text-sm font-medium leading-none text-gray-900 dark:text-white',
                    ])
                </strong>

                <small class="block text-xs text-gray-500 leading-none mt-0.5">
                    {{ __('adminhub::account.view-profile') }}
                </small>
            </div>
        </div>
    </div>
</div>
