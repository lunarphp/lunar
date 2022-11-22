<div>
    <x-hub::menu handle="sidebar"
                 current="{{ request()->route()->getName() }}">
        <ul class="px-2 space-y-4">
            @foreach ($component->items as $item)
                <li>
                    <a href="{{ route($item->route) }}"
                       @class([
                           'menu-link',
                           'menu-link--active' => $item->isActive(
                               $component->attributes->get('current')
                           ),
                           'menu-link--inactive' => !$item->isActive(
                               $component->attributes->get('current')
                           ),
                       ])
                       :class="{ 'justify-center': !showExpandedMenu }">
                        <span x-cloak>
                            {!! $item->renderIcon('w-5 h-5') !!}
                        </span>

                        <span x-cloak
                              x-show="showExpandedMenu"
                              class="text-sm font-medium">
                            {{ $item->name }}
                        </span>
                    </a>
                </li>
            @endforeach

            @foreach ($component->sections as $section)
                <li x-data="{ showSubMenu: false }"
                    class="relative">
                    <button x-cloak
                            x-show="!showExpandedMenu"
                            x-on:click.prevent="!showExpandedMenu && (showSubMenu = !showSubMenu)"
                            class="absolute z-10 p-1 -ml-1 text-gray-600 -translate-y-1/2 bg-white border border-gray-200 rounded top-1/2 left-full">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 20"
                             fill="currentColor"
                             class="w-3 h-3">
                            <path
                                  d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM11.5 15.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" />
                        </svg>
                    </button>

                    <p x-cloak
                       x-show="showExpandedMenu"
                       class="text-xs font-bold tracking-wide text-gray-600 uppercase">
                        {{ $section->name }}
                    </p>

                    @if (count($section->getItems()))
                        <ul x-cloak
                            x-show="showExpandedMenu || showSubMenu"
                            x-on:click.away="showSubMenu = false"
                            class="mt-1">
                            @foreach ($section->getItems() as $item)
                                <li class="my-1">
                                    <a href="{{ route($item->route) }}"
                                       @class([
                                           'menu-link',
                                           'menu-link--active' => $item->isActive(
                                               $component->attributes->get('current')
                                           ),
                                           'menu-link--inactive' => !$item->isActive(
                                               $component->attributes->get('current')
                                           ),
                                       ])>
                                        <span x-cloak>
                                            {!! $item->renderIcon('w-5 h-5') !!}
                                        </span>

                                        <span x-cloak
                                              x-show="showExpandedMenu || showSubMenu"
                                              class="text-sm font-medium">
                                            {{ $item->name }}
                                        </span>
                                    </a>

                                    <ul class="pl-4 ml-4 space-y-1 border-l border-gray-100">
                                        <li>
                                            <a href="#"
                                               class="menu-link menu-link--inactive">
                                                <span class="text-xs">
                                                    Test
                                                </span>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="#"
                                               class="menu-link menu-link--inactive">
                                                <span class="text-xs">
                                                    Test
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach

            @if (Auth::user()->can('settings'))
                <li class="my-1">
                    <a href="{{ route('hub.settings') }}"
                       @class([
                           'menu-link',
                           'menu-link--active' => Str::contains(request()->url(), 'settings'),
                           'menu-link--inactive' => !Str::contains(request()->url(), 'settings'),
                       ])
                       :class="{ 'group justify-center': !showExpandedMenu }">
                        <span x-cloak>
                            {!! Lunar\Hub\LunarHub::icon('cog', 'w-5 h-5') !!}
                        </span>

                        <span x-cloak
                              x-show="showExpandedMenu"
                              class="text-sm font-medium">
                            {{ __('adminhub::global.settings') }}
                        </span>
                    </a>
                </li>
            @endif
        </ul>
    </x-hub::menu>
</div>
