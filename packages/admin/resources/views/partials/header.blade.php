<div class="flex items-center justify-between h-16 px-4 bg-white sm:px-6 lg:px-8">
    <a href="{{ url()->previous() }}"
       class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-5 h-5"
             viewBox="0 0 20 20"
             fill="currentColor">
            <path fill-rule="evenodd"
                  d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                  clip-rule="evenodd" />
        </svg>

        <span class="text-sm font-medium"> Back </span>
    </a>

    <div x-data="{ showUserMenu: false }"
         x-on:mouseover="showUserMenu = true"
         x-on:mouseleave="showUserMenu = false"
         class="relative">
        <div x-show="showUserMenu"
             x-transition
             class="absolute p-2 -mt-2 bg-white border border-gray-100 rounded-lg top-full right-4 w-36">
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

        <div class="flex items-center h-16 gap-2">
            <div class="shrink-0">
                @livewire('hub.components.avatar')
            </div>

            <div class="leading-none">
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
</div>
