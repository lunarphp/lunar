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

    <div class="flex items-center gap-2">
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
