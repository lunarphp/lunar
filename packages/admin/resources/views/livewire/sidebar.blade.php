<div>
    <x-hub::menu handle="sidebar"
                 current="{{ request()->route()->getName() }}">
        <ul class="sidebar-container flex flex-col space-y-2">
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
                       ])>
                        {!! $item->renderIcon('w-5 h-5') !!}

                        <span class="sidebar-title text-sm font-medium">
                            {{ $item->name }}
                        </span>

                        <span class="sidebar-title-tooltip absolute z-10 p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded dark:bg-gray-800 w-28 left-full">
                            {{ $item->name }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </x-hub::menu>

    @if (Auth::user()->can('settings'))
        <div class="sidebar-container flex flex-col w-full pt-4 mt-4 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ route('hub.settings') }}"
               @class([
                   'menu-link',
                   'menu-link--active' => Str::contains(request()->url(), 'settings'),
                   'menu-link--inactive' => !Str::contains(request()->url(), 'settings'),
               ])>
                {!! GetCandy\Hub\GetCandyHub::icon('cog', 'w-5 h-5') !!}

                <span class="sidebar-title text-sm font-medium">
                    {{ __('adminhub::global.settings') }}
                </span>

                <span class="sidebar-title-tooltip absolute z-10 p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded dark:bg-gray-800 w-28 left-full">
                    {{ __('adminhub::global.settings') }}
                </span>
            </a>
        </div>
    @endif
</div>
