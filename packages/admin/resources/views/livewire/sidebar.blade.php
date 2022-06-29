<div>
    <x-hub::menu handle="sidebar"
                 current="{{ request()->route()->getName() }}">
        <ul class="space-y-2">
            @foreach ($component->items as $item)
                <li>
                    <a href="{{ route($item->route) }}"
                       @class([
                           'relative flex items-center gap-2 p-2 rounded text-gray-500',
                           'bg-blue-50 text-blue-700 hover:text-blue-600' => $item->isActive(
                               $component->attributes->get('current')
                           ),
                           'hover:bg-blue-50 hover:text-blue-700' => !$item->isActive(
                               $component->attributes->get('current')
                           ),
                       ])
                       x-data="{ showTooltip: false }"
                       x-on:mouseover="showTooltip = showExpandedMenu ? false : true"
                       x-on:mouseleave="showTooltip = false">
                        {!! $item->renderIcon('w-5 h-5') !!}

                        <span x-show="showExpandedMenu"
                              x-cloak
                              class="text-sm font-medium">
                            {{ $item->name }}
                        </span>

                        <span x-show="showTooltip"
                              x-transition
                              x-cloak
                              class="absolute z-10 p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded w-28 left-full">
                            {{ $item->name }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </x-hub::menu>

    <div class="pt-4 mt-4 border-t border-gray-100">
        @if (Auth::user()->can('settings'))
            <a href="{{ route('hub.settings') }}"
               @class([
                   'relative flex items-center gap-2 p-2 rounded text-gray-500',
                   'bg-blue-50 text-blue-700 hover:text-blue-600' => Str::contains(
                       request()->url(),
                       'settings'
                   ),
                   'hover:bg-blue-50 hover:text-blue-700' => !Str::contains(
                       request()->url(),
                       'settings'
                   ),
               ])
               x-data="{ showTooltip: false }"
               x-on:mouseover="showTooltip = showExpandedMenu ? false : true"
               x-on:mouseleave="showTooltip = false">
                {!! GetCandy\Hub\GetCandyHub::icon('cog', 'w-5 h-5') !!}

                <span x-show="showExpandedMenu"
                      class="text-sm font-medium">
                    {{ __('adminhub::global.settings') }}
                </span>

                <span x-show="showTooltip"
                      x-transition
                      x-cloak
                      class="absolute z-10 p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded w-28 left-full">
                    {{ __('adminhub::global.settings') }}
                </span>
            </a>
        @endif
    </div>
</div>
