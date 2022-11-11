<div class="hidden lg:flex lg:flex-shrink-0">
    <div class="relative flex flex-col justify-between bg-white border-r border-gray-100 dark:bg-gray-900 dark:border-gray-800 app-sidemenu"
         :class="{
             'w-64': showExpandedMenu,
             'w-20': !showExpandedMenu
         }">
        <div class="flex-1">
            <a href="{{ route('hub.index') }}"
               class="flex items-center w-full h-16 px-2">
                <x-hub::branding.logo x-cloak
                                      x-show="showExpandedMenu" />
                <x-hub::branding.logo x-cloak
                                      x-show="!showExpandedMenu"
                                      iconOnly />
            </a>

            <div class="w-full px-2 py-4 border-t border-gray-100 dark:border-gray-800">
                @livewire('sidebar')
            </div>
        </div>

        <div class="p-4 border-t border-gray-100 shrink-0">
            <p class="text-xs text-center text-gray-500">
                <span x-cloak
                      x-show="showExpandedMenu">
                    Lunar
                </span>

                <x-hub::lunar.version />
            </p>
        </div>

        <button x-on:click="showExpandedMenu = !showExpandedMenu"
                class="absolute z-50 p-1 -ml-[11px] text-gray-600 bg-white border border-gray-200 rounded left-full bottom-16">
            <span :class="{ '-rotate-180': showExpandedMenu }"
                  class="block">
                <x-hub::icon ref="chevron-right"
                             class="w-3 h-3"
                             style="solid" />
            </span>
        </button>
    </div>
</div>
