<!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
<div class="fixed inset-0 z-40 flex md:hidden" role="dialog" aria-modal="true" x-cloak x-show="showMenu"
    x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75" aria-hidden="true" @click="showMenu = false"></div>

    <div class="relative flex w-full max-w-xs flex-1 flex-col bg-white pt-5 dark:bg-slate-900 dark:text-white"
        x-show="showMenu" x-transition:enter="transition ease-in-out duration-300"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full">

        <div class="absolute top-0 right-0 -mr-12 pt-2">
            <button @click="showMenu = false"
                class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                <span class="sr-only">{{ __('adminhub::menu.close-sidebar') }}</span>
                <!-- Heroicon name: outline/x -->
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="my-2 flex flex-shrink-0 items-center px-8">
            <x-adminhub::logo class="my-auto w-auto" />
            <x-adminhub::theme-switcher class="ml-auto" />
        </div>
        <div class="mt-5 h-0 flex-1 overflow-y-auto">
            <nav class="space-y-1 px-2">
                @livewire('sidebar')
            </nav>
        </div>
        <div
            class="flex flex-shrink-0 border-t border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-slate-900 dark:text-white">

            <a href="{{ route('hub.account') }}" class="group flex flex-shrink-0 grow items-center">
                <div>
                    @livewire('hub.components.avatar')
                </div>
                <div class="ml-3">
                    @livewire('hub.components.current-staff-name', [
                        'class' => 'text-sm font-medium text-gray-700 dark:text-gray-200 dark:group-hover:text-gray-400 group-hover:text-gray-900 truncate w-32',
                    ])
                    <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                        {{ __('adminhub::account.view-profile') }}
                    </p>
                </div>
            </a>
            <div class="flex aspect-square h-full items-center justify-center">
                <form method="POST" class="group h-8 w-8 p-1" action="{{ route('hub.logout') }}">
                    @csrf
                    <button>
                        <svg viewBox="0 0 24 24" fill="none"
                            class="text-gray-700 group-hover:text-gray-900 dark:text-gray-200 dark:group-hover:text-gray-400"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 11V13L17 13V16L22 12L17 8V11L8 11Z" fill="currentColor" />
                            <path
                                d="M4 21H13C14.103 21 15 20.103 15 19V15H13V19H4L4 5H13V9H15V5C15 3.897 14.103 3 13 3H4C2.897 3 2 3.897 2 5L2 19C2 20.103 2.897 21 4 21Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Static sidebar for desktop -->
<div class="hidden md:flex md:flex-shrink-0">
    <div class="w-50 flex flex-col">
        <div class="flex grow flex-col overflow-y-auto bg-white pt-5 pb-4 dark:bg-slate-900 dark:text-white">
            <div class="mx-4 my-2 flex flex-shrink-0 items-center px-4">
                <x-adminhub::logo class="my-auto w-auto" />
                <x-adminhub::theme-switcher class="ml-auto" />
            </div>
            <nav class="mt-5 flex grow flex-col gap-y-1">
                @livewire('sidebar')
            </nav>
        </div>
        <div
            class="flex flex-shrink-0 border-t border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-slate-900 dark:text-white">

            <a href="{{ route('hub.account') }}" class="group flex flex-shrink-0 grow items-center">
                <div>
                    @livewire('hub.components.avatar')
                </div>
                <div class="ml-3">
                    @livewire('hub.components.current-staff-name', [
                        'class' => 'text-sm font-medium text-gray-700 dark:text-gray-200 dark:group-hover:text-gray-400 group-hover:text-gray-900 truncate w-32',
                    ])
                    <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                        {{ __('adminhub::account.view-profile') }}
                    </p>
                </div>
            </a>
            <div class="flex aspect-square h-full items-center justify-center">
                <form method="POST" class="group h-8 w-8 p-1" action="{{ route('hub.logout') }}">
                    @csrf
                    <button>
                        <svg viewBox="0 0 24 24" fill="none"
                            class="text-gray-700 group-hover:text-gray-900 dark:text-gray-200 dark:group-hover:text-gray-400"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 11V13L17 13V16L22 12L17 8V11L8 11Z" fill="currentColor" />
                            <path
                                d="M4 21H13C14.103 21 15 20.103 15 19V15H13V19H4L4 5H13V9H15V5C15 3.897 14.103 3 13 3H4C2.897 3 2 3.897 2 5L2 19C2 20.103 2.897 21 4 21Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
