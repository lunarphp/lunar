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
                           'menu-link--inactive !text-gray-700' => !$item->isActive(
                               $component->attributes->get('current')
                           ),
                       ])>
                        <span x-cloak
                              :class="{ 'mx-auto': !showExpandedMenu }">
                            {!! $item->renderIcon('w-5 h-5') !!}
                        </span>

                        <span x-cloak
                              x-show="showExpandedMenu"
                              class="text-sm font-medium"
                              :class="{
                                  'absolute left-[calc(100%_+_4px)] m-auto bg-black z-50 text-white rounded py-1.5 px-3 group-hover:!block':
                                      !showExpandedMenu,
                              }">
                            {{ $item->name }}
                        </span>
                    </a>
                </li>
            @endforeach

            @foreach ($component->groups as $group)
                <li class="relative">
                    <header x-cloak
                            x-show="showExpandedMenu"
                            class="text-sm font-semibold text-gray-600">
                        {{ $group->name }}
                    </header>

                    @if (count($group->getItems()))
                        <ul class="mt-1 space-y-1">
                            @foreach ($group->getItems() as $item)
                                <li class="relative">
                                    <a href="{{ route($item->route) }}"
                                       @class([
                                           'menu-link group',
                                           'menu-link--active' => $item->isActive(
                                               $component->attributes->get('current')
                                           ),
                                           'menu-link--inactive !text-gray-700' => !$item->isActive(
                                               $component->attributes->get('current')
                                           ),
                                       ])>
                                        <span x-cloak
                                              :class="{ 'mx-auto': !showExpandedMenu }">
                                            {!! $item->renderIcon('w-5 h-5') !!}
                                        </span>

                                        <span x-cloak
                                              x-show="showExpandedMenu"
                                              class="text-sm font-medium"
                                              :class="{
                                                  'absolute left-[calc(100%_+_4px)] m-auto bg-black z-50 text-white rounded py-1.5 px-3 group-hover:!block':
                                                      !showExpandedMenu,
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
                            class="mt-1 space-y-1">
                            @foreach ($group->getSections() as $section)
                                <li x-data="{
                                    showSubMenu: false,
                                    hasActiveItem: {{ $section->hasActive($component->attributes->get('current')) ? 'true' : 'false' }},
                                    init() {
                                        this.showSubMenu = (showExpandedMenu && this.hasActiveItem) ? true : this.showSubMenu
                                        this.$watch('showExpandedMenu', (isExpanded) => this.showSubMenu = (isExpanded && this.hasActiveItem) ? true : this.showSubMenu)
                                    },
                                }"
                                    class="relative">
                                    @if (count($section->getItems()))
                                        <span class="absolute z-10 top-0 left-[calc(100%_+_4px)]">
                                            <button x-cloak
                                                    x-show="!showExpandedMenu"
                                                    x-on:click.prevent="!showExpandedMenu && (showSubMenu = !showSubMenu)"
                                                    class="p-1 text-gray-600 bg-white border border-gray-200 rounded">
                                                <x-hub::icon ref="menu"
                                                             class="w-3 h-3" />
                                            </button>
                                        </span>
                                    @endif

                                    <a href="{{ route($section->route) }}"
                                       @class([
                                           'menu-link group',
                                           'menu-link--active' => $section->isActive(
                                               $component->attributes->get('current')
                                           ),
                                           'menu-link--inactive !text-gray-700' => !$section->isActive(
                                               $component->attributes->get('current')
                                           ),
                                       ])>
                                        <span x-cloak
                                              :class="{ 'mx-auto': !showExpandedMenu }">
                                            {!! $section->renderIcon('w-5 h-5') !!}
                                        </span>

                                        <span x-cloak
                                              x-show="showExpandedMenu"
                                              class="text-sm font-medium"
                                              :class="{
                                                  'absolute left-[calc(100%_+_4px)] m-auto bg-black z-50 text-white rounded py-1.5 px-3 group-hover:!block':
                                                      !showExpandedMenu,
                                              }">
                                            {{ $section->name }}
                                        </span>

                                        @if (count($section->getItems()))
                                            <button x-cloak
                                                    x-show="showExpandedMenu"
                                                    x-on:click.prevent="showSubMenu = !showSubMenu"
                                                    class="p-1 ml-auto text-gray-600 bg-white border border-gray-200 rounded">
                                                <span :class="{ '-rotate-180': showSubMenu }"
                                                      class="block transition">
                                                    <x-hub::icon ref="chevron-down"
                                                                 class="w-3 h-3" />
                                                </span>
                                            </button>
                                        @endif
                                    </a>

                                    @if (count($section->getItems()))
                                        <div x-show="showSubMenu"
                                             class="bg-white"
                                             :class="{
                                                 'absolute top-0 left-[calc(100%_+_40px)] shadow-sm z-50 rounded': !
                                                     showExpandedMenu && showSubMenu
                                             }">
                                            <ul class="space-y-1"
                                                :class="{
                                                    'border-l border-gray-100 ml-[18px] pl-[18px] mt-1': showExpandedMenu &&
                                                        showSubMenu,
                                                    'w-64 p-4': !showExpandedMenu && showSubMenu
                                                }">
                                                @foreach ($section->getItems() as $item)
                                                    <li>
                                                        <a href="{{ route($item->route) }}"
                                                           @class([
                                                               'flex text-sm font-medium',
                                                               'text-blue-600 hover:text-blue-500' => $item->isActive(
                                                                   $component->attributes->get('current')
                                                               ),
                                                               'text-gray-500 hover:text-blue-600' => !$item->isActive(
                                                                   $component->attributes->get('current')
                                                               ),
                                                           ])>
                                                            {{ $item->name }}
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
        </ul>

        @if (Auth::user()->can('settings'))
            <div class="pt-4 mt-4 border-t border-gray-100">
                <a href="{{ route('hub.settings') }}"
                   @class([
                       'menu-link group',
                       'menu-link--active' => Str::contains(request()->url(), 'settings'),
                       'menu-link--inactive !text-gray-700' => !Str::contains(
                           request()->url(),
                           'settings'
                       ),
                   ])
                   :class="{ 'group justify-center': !showExpandedMenu }">
                    <span x-cloak
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
            </div>
        @endif
    </x-hub::menu>
</div>
