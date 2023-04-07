<div x-data="{ showUserMenu: false }"
     x-on:mouseover="showUserMenu = true"
     x-on:mouseleave="showUserMenu = false"
     class="relative">
    <div x-cloak
         x-transition
         x-show="showUserMenu"
         class="absolute z-50 p-2 -mt-1 bg-white border border-gray-100 rounded-lg dark:bg-gray-900 dark:border-gray-800 top-full right-4 w-40">
        <ul>
            <li class="p-2">
                <a href="{{ route('hub.account') }}"
                   class="menu-link menu-link--inactive hover:text-sky-500">
                    <span class="text-sm font-medium">
                        {{ __('adminhub::account.view-profile') }}
                    </span>
                </a>
            </li>

            <li class="p-2">
                <form method="POST"
                      action="{{ route('hub.logout') }}">
                    @csrf
                    <button type="submit"
                            class="menu-link menu-link--inactive hover:text-sky-500">
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

    <div class="flex items-center gap-2">
        <div class="shrink-0">
            @livewire('hub.components.avatar')
        </div>
    </div>
</div>
