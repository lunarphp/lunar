<div class="hidden lg:flex lg:flex-shrink-0">
    <div class="relative flex flex-col bg-white border-r border-gray-100"
         :class="{ 'w-48': showExpandedMenu, 'w-20 items-center': !showExpandedMenu }">
        <a href="{{ route('hub.index') }}"
           class="flex items-center w-full h-16 px-4 border-b border-gray-100">
            <img src="https://markmead.dev/gc-logo.svg"
                 alt="GetCandy Logo"
                 class="w-auto h-10"
                 x-show="showExpandedMenu"
                 x-cloak />

            <img src="https://markmead.dev/gc-favicon.svg"
                 alt="GetCandy Logo"
                 class="w-8 h-8 mx-auto"
                 x-show="!showExpandedMenu"
                 x-cloak />
        </a>

        <div class="px-4 pt-4">
            @livewire('sidebar')
        </div>

        <button x-on:click="showExpandedMenu = !showExpandedMenu"
                class="absolute z-50 p-1 bg-white border border-gray-100 rounded -right-[13px] bottom-8 text-gray-600 hover:text-gray-700">
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
