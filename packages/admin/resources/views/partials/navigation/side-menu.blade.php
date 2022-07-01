<div class="hidden lg:flex lg:flex-shrink-0">
    <div class="relative flex flex-col bg-white border-r border-gray-100 dark:bg-gray-900 dark:border-gray-800"
         :class="{ 'w-64': showExpandedMenu, 'w-20': !showExpandedMenu }">
        <a href="{{ route('hub.index') }}"
           class="flex items-center w-full h-16 px-4">
            <img src="https://markmead.dev/gc-logo.svg"
                 alt="GetCandy Logo"
                 class="w-auto h-10"
                 x-show="showExpandedMenu && !darkMode"
                 x-cloak />

            <img src="https://markmead.dev/gc-logo-white.svg"
                 alt="GetCandy Logo"
                 class="w-auto h-10"
                 x-show="showExpandedMenu && darkMode"
                 x-cloak />

            <img src="https://markmead.dev/gc-favicon.svg"
                 alt="GetCandy Logo"
                 class="w-8 h-8 mx-auto"
                 x-show="!showExpandedMenu"
                 x-cloak />
        </a>

        <div class="w-full px-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            @livewire('sidebar')
        </div>

        <button x-on:click="showExpandedMenu = !showExpandedMenu"
                class="absolute z-50 p-1 bg-white border border-gray-100 dark:border-gray-700 dark:bg-gray-800 rounded -right-[13px] bottom-8 text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
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
    </div>
</div>
