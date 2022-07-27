<div class="space-y-4" x-data="{ menuType: '{{ $menuType }}' }">
    @if (count($items))
        <div>
            <ul class="ml-1 space-y-2">
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

                            <span class="text-sm font-medium"
                                  x-cloak
                                  x-show="menuType == 'main_menu' && showExpandedMenu">
                                {{ $item->name }}
                            </span>

                            <span x-cloak
                                  x-transition
                                  x-show="showTooltip"
                                  class="absolute z-10 p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded dark:bg-gray-800 w-28 left-full">
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
                <header class="text-sm font-semibold text-gray-600"
                        x-cloak
                        x-show="menuType == 'main_menu' && showExpandedMenu || menuType == 'sub_menu'">
                    {{ $section->name }}
                </header>

                <ul class="ml-1 mt-2 space-y-2 flex flex-col">
                    @foreach ($section->getItems() as $item)
                        <li>
                            <a href="{{ route($item->route) }}"
                               x-data="{ showTooltip: false }"
                               x-on:mouseover="showTooltip = showExpandedMenu ? false : true"
                               x-on:mouseleave="showTooltip = false"
                               @class([
                                   'flex items-center gap-2 p-2 rounded text-gray-500',
                                   'bg-blue-50 text-blue-700 hover:text-blue-600' => $item->isActive($active),
                                   'hover:bg-blue-50 hover:text-blue-700' => !$item->isActive($active),
                               ])>
                                {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                                <span class="text-sm font-medium"
                                      x-cloak
                                      x-show="menuType == 'sub_menu' || showExpandedMenu">
                                    {{ $item->name }}
                                </span>

                                <span x-cloak
                                      x-transition
                                      x-show="showTooltip"
                                      class="absolute z-10 p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded dark:bg-gray-800 w-28 left-full">
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
