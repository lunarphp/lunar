<div class="relative z-40 lg:hidden"
     role="dialog"
     aria-modal="true">
    <div class="fixed inset-0 bg-gray-600/75"
         x-show="showMobileMenu"></div>

    <div class="fixed inset-0 z-40 flex"
         x-show="showMobileMenu"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full">
        <div class="w-full max-w-xs p-4 bg-white focus:outline-none"
             x-on:click.away="showMobileMenu = false">
            <div class="flex items-center justify-between">
                <a href="{{ route('hub.index') }}"
                   class="block">
                    <img class="w-auto h-8"
                         src="https://markmead.dev/gc-logo.svg"
                         alt="GetCandy Logo">
                </a>

                <button type="button"
                        x-on:click="showMobileMenu = false">
                    <svg class="w-5 h-5"
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

            <div class="mt-8">
                <x-hub::menu handle="sidebar"
                             current="{{ request()->route()->getName() }}">
                    <ul class="space-y-2">
                        @foreach ($component->items as $item)
                            <li x-data="{ showAccordionMenu: false }">
                                <a href="{{ route($item->route) }}"
                                   @class([
                                       'flex items-center gap-2 p-2 rounded text-gray-500',
                                       'bg-blue-50 text-blue-700' => request()->routeIs($item->route),
                                   ])>
                                    {!! $item->renderIcon('w-5 h-5') !!}

                                    <span class="text-sm font-medium">
                                        {{ $item->name }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-hub::menu>

                <div class="pt-4 mt-4 border-t border-gray-100">
                    @if (Auth::user()->can('settings'))
                        <div x-data="{ showSettingsMenu: false }">
                            <a href="{{ route('hub.settings') }}"
                               @class([
                                   'p-2 rounded text-gray-500 flex items-center justify-between',
                                   'bg-blue-50 text-blue-700' => Str::contains(request()->url(), 'settings'),
                               ])>
                                <span class="flex items-center gap-2">
                                    {!! GetCandy\Hub\GetCandyHub::icon('cog', 'w-5 h-5') !!}

                                    <span class="text-sm font-medium">
                                        {{ __('adminhub::global.settings') }}
                                    </span>
                                </span>

                                <button x-on:click.prevent="showSettingsMenu = !showSettingsMenu"
                                        class="p-0.5 text-gray-600 bg-white rounded hover:text-gray-700">
                                    <span x-show="showSettingsMenu">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-4 h-4"
                                             viewBox="0 0 20 20"
                                             fill="currentColor">
                                            <path fill-rule="evenodd"
                                                  d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"
                                                  clip-rule="evenodd" />
                                        </svg>
                                    </span>

                                    <span x-show="!showSettingsMenu">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-4 h-4"
                                             viewBox="0 0 20 20"
                                             fill="currentColor">
                                            <path fill-rule="evenodd"
                                                  d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                                  clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                            </a>

                            <div x-show="showSettingsMenu"
                                 class="mt-2 ml-4">
                                <x-hub::menu handle="settings"
                                             current="{{ request()->route()->getName() }}">
                                    <ul class="space-y-0.5">
                                        @foreach ($component->items as $item)
                                            <li>
                                                <a href="{{ route($item->route) }}"
                                                   @class([
                                                       'p-2 rounded block text-gray-500 text-xs font-medium',
                                                       'bg-blue-50 text-blue-700' => $item->isActive(
                                                           $component->attributes->get('current')
                                                       ),
                                                   ])>
                                                    {{ $item->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </x-hub::menu>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
