<div class="space-y-4"
     x-data="{ menuType: '{{ $menuType }}' }">
    @if (count($items))
        <div>
            <ul class="flex flex-col space-y-2"
                :class="{ 'items-center': (!showExpandedMenu || settingsPanelOpen) && menuType === 'main_menu' }">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ route($item->route) }}"
                           x-data="{ showTooltip: false }"
                           x-on:mouseover="showTooltip = showExpandedMenu ? false : true"
                           x-on:mouseleave="showTooltip = false"
                           @class([
                               'menu-link',
                               'menu-link--active' => $item->isActive($active),
                               'menu-link--inactive' => !$item->isActive($active),
                           ])>
                            {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                            <span x-cloak
                                  x-show="menuType == 'main_menu' && (showExpandedMenu && !settingsPanelOpen)"
                                  class="text-sm font-medium">
                                {{ $item->name }}
                            </span>

                            <span
                                  class="absolute z-10 invisible p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded dark:bg-gray-800 w-28 left-full group-hover:visible">
                                {{ $item->name }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse ($sections as $section)
        @if (count($section->getItems()))
            <div>
                <header x-cloak
                        x-show="menuType == 'main_menu' && (showExpandedMenu && !settingsPanelOpen) || menuType == 'sub_menu'"
                        class="text-sm font-semibold text-gray-600">
                    {{ $section->name }}
                </header>

                <ul class="flex flex-col mt-2 space-y-2"
                    :class="{ 'items-center': (!showExpandedMenu || settingsPanelOpen) && menuType === 'main_menu' }">
                    @foreach ($section->getItems() as $item)
                        <li>
                            <a href="{{ route($item->route) }}"
                               @class([
                                   'menu-link',
                                   'menu-link--active' => $item->isActive($active),
                                   'menu-link--inactive' => !$item->isActive($active),
                               ])
                               :class="{ 'group': !showExpandedMenu || settingsPanelOpen }">
                                {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                                <span x-cloak
                                      x-show="menuType == 'sub_menu' || (showExpandedMenu && !settingsPanelOpen)"
                                      class="text-sm font-medium">
                                    {{ $item->name }}
                                </span>

                                <span
                                      class="absolute z-10 invisible p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded dark:bg-gray-800 w-28 left-full group-hover:visible">
                                    {{ $item->name }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @empty
    @endforelse
</div>
