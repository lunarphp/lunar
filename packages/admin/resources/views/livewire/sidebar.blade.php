<div>
    <x-hub::menu handle="sidebar"
                 current="{{ request()->route()->getName() }}">
        <x-hub::menu-list menuType="main_menu"
                          :sections="$component->sections"
                          :items="$component->items"
                          :active="$component->attributes->get('current')" />
    </x-hub::menu>

    @if (Auth::user()->can('settings'))
        <div class="flex flex-col w-full pt-4 mt-4 border-t border-gray-100 dark:border-gray-800"
             :class="{ 'items-center': !showExpandedMenu }">
            <a href="{{ route('hub.settings') }}"
               @class([
                   'menu-link',
                   'menu-link--active' => Str::contains(request()->url(), 'settings'),
                   'menu-link--inactive' => !Str::contains(request()->url(), 'settings'),
               ])
               :class="{ 'group': !showExpandedMenu }">
                {!! GetCandy\Hub\GetCandyHub::icon('cog', 'w-5 h-5') !!}

                <span x-cloak
                      x-show="showExpandedMenu"
                      class="text-sm font-medium">
                    {{ __('adminhub::global.settings') }}
                </span>

                <span
                      class="invisible absolute z-10 p-2 ml-4 text-xs text-center text-white bg-gray-900 rounded dark:bg-gray-800 w-28 left-full group-hover:visible">
                    {{ __('adminhub::global.settings') }}
                </span>
            </a>
        </div>
    @endif
</div>
