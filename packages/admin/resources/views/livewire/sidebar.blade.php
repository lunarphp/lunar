<div>
    <x-hub::menu handle="sidebar"
                 current="{{ request()->route()->getName() }}">
        <ul class="px-2 space-y-2">
            @foreach ($component->items as $item)
                <li class="relative">
                    <a href="{{ route($item->route) }}"
                       @class([
                           'menu-link group',
                           'menu-link--active' => $item->isActive(
                               $component->attributes->get('current')
                           ),
                           'menu-link--inactive' => !$item->isActive(
                               $component->attributes->get('current')
                           ),
                       ])
                       :class="{ 'justify-center': !showExpandedMenu }">
                        <span x-cloak
                              class="block"
                              :class="{ 'mx-auto': !showExpandedMenu }">
                            {!! $item->renderIcon('w-5 h-5') !!}
                        </span>

                        <span x-cloak
                              x-show="showExpandedMenu"
                              class="font-medium group-hover:!block"
                              :class="{
                                  'absolute top-1/2 -translate-y-1/2 left-full ml-2 bg-blue-700 z-50 text-white rounded py-1.5 px-3 text-xs':
                                      !showExpandedMenu,
                                  'text-sm': showExpandedMenu
                              }">
                            {{ $item->name }}
                        </span>
                    </a>
                </li>
            @endforeach

            @foreach ($component->groups as $group)
                <li x-data="{ showSubMenu: false }"
                    class="relative">
                    <p x-cloak
                       x-show="showExpandedMenu"
                       class="text-xs font-bold tracking-wide text-gray-600 uppercase">
                        {{ $group->name }}
                    </p>

                    @if (count($group->getItems()))
                        <ul x-cloak
                            class="mt-2"
                            :class="{ 'space-y-0.5': showExpandedMenu, 'space-y-2': !showExpandedMenu }">
                            @foreach ($group->getItems() as $item)
                                <li class="relative">
                                    <a href="{{ route($item->route) }}"
                                       @class([
                                           'menu-link group',
                                           'menu-link--active' => $item->isActive(
                                               $component->attributes->get('current')
                                           ),
                                           'menu-link--inactive' => !$item->isActive(
                                               $component->attributes->get('current')
                                           ),
                                       ])>
                                        <span x-cloak
                                              class="block"
                                              :class="{ 'mx-auto': !showExpandedMenu }">
                                            {!! $item->renderIcon('w-5 h-5') !!}
                                        </span>

                                        <span x-cloak
                                              x-show="showExpandedMenu"
                                              class="font-medium group-hover:!block"
                                              :class="{
                                                  'absolute top-1/2 -translate-y-1/2 left-full ml-2 bg-blue-700 z-50 text-white rounded py-1.5 px-3 text-xs':
                                                      !showExpandedMenu,
                                                  'text-sm': showExpandedMenu
                                              }">
                                            {{ $item->name }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if (count($group->getSections()))
                        <ul x-cloak
                            class="mt-2"
                            :class="{ 'space-y-0.5': showExpandedMenu, 'space-y-2': !showExpandedMenu }">
                            @foreach ($group->getSections() as $section)
                                <li class="relative">
                                    @if (count($section->getItems()))
                                        <button x-cloak
                                                x-show="!showExpandedMenu"
                                                x-on:click.prevent="!showExpandedMenu && (showSubMenu = !showSubMenu)"
                                                class="absolute z-10 p-1 ml-1.5 text-gray-600 bg-white border border-gray-200 rounded top-0 left-full">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 20 20"
                                                 fill="currentColor"
                                                 class="w-3 h-3">
                                                <path
                                                      d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM11.5 15.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" />
                                            </svg>
                                        </button>
                                    @endif

                                    <a href="{{ route($section->route) }}"
                                       @class([
                                           'menu-link group',
                                           'menu-link--active' => $section->isActive(
                                               $component->attributes->get('current')
                                           ),
                                           'menu-link--inactive' => !$section->isActive(
                                               $component->attributes->get('current')
                                           ),
                                       ])>
                                        <span x-cloak
                                              class="block"
                                              :class="{ 'mx-auto': !showExpandedMenu }">
                                            {!! $section->renderIcon('w-5 h-5') !!}
                                        </span>

                                        <span x-cloak
                                              x-show="showExpandedMenu"
                                              class="font-medium group-hover:!block"
                                              :class="{
                                                  'absolute top-1/2 -translate-y-1/2 left-full ml-2 bg-blue-700 z-50 text-white rounded py-1.5 px-3 text-xs':
                                                      !showExpandedMenu,
                                                  'text-sm': showExpandedMenu,
                                                  'group-hover:!hidden': showSubMenu
                                              }">
                                            {{ $section->name }}
                                        </span>
                                    </a>

                                    @if (count($section->getItems()))
                                        <div x-show="showExpandedMenu || showSubMenu"
                                             class="bg-white"
                                             :class="{ 'absolute top-0 left-full ml-10 shadow-sm z-50 rounded': showSubMenu }"
                                             x-on:click.away="showSubMenu = false"
                                             x-on:keydown.escape.window="showSubMenu = false">
                                            <ul class="space-y-0.5"
                                                :class="{ 'border-l border-gray-100 ml-4 pl-4 mt-2': showExpandedMenu, 'w-64 p-2': showSubMenu }">
                                                @foreach ($section->getItems() as $item)
                                                    <li class="relative">
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
                                                            <span class="text-sm font-medium">
                                                                {{ $item->name }}
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
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
                           'menu-link group',
                           'menu-link--active' => Str::contains(request()->url(), 'settings'),
                           'menu-link--inactive' => !Str::contains(request()->url(), 'settings'),
                       ])
                       :class="{ 'group justify-center': !showExpandedMenu }">
                        <span x-cloak
                              class="block"
                              :class="{ 'mx-auto': !showExpandedMenu }">
                            {!! Lunar\Hub\LunarHub::icon('cog', 'w-5 h-5') !!}
                        </span>

                        <span x-cloak
                              x-show="showExpandedMenu"
                              class="font-medium group-hover:!block"
                              :class="{
                                  'absolute top-1/2 -translate-y-1/2 left-full ml-2 bg-blue-700 z-50 text-white rounded py-1.5 px-3 text-xs':
                                      !showExpandedMenu,
                                  'text-sm': showExpandedMenu
                              }">
                            {{ __('adminhub::global.settings') }}
                        </span>
                    </a>
                </li>
            @endif
        </ul>
    </x-hub::menu>
</div>
