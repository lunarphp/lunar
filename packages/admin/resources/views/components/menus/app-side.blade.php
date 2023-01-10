<div class="overflow-y-auto h-full bg-gray-800">
    <div class="border-t border-b border-gray-700 pt-4 p-4">
        <x-hub::menu handle="sidebar" current="{{ request()->route()->getName() }}">
            @foreach ($component->items as $item)
                <x-hub::menus.app-side.link
                    :item="$item"
                    :active="$item->isActive(
                        $component->attributes->get('current')
                    )"
                />
            @endforeach

            @foreach ($component->groups as $group)
                <x-hub::menus.app-side.group
                    :group="$group"
                    :current="$component->attributes->get('current')"
                />
            @endforeach
        </x-hub::menu>
    </div>

    @if (Auth::user()->can('settings'))
        <div class="p-4 border-t border-gray-900 bottom-0 bg-gray-800">
            <a href="{{ route('hub.settings') }}"
               @class([
                   'flex items-center space-x-2 text-sm rounded group',
                   'text-white font-semibold' => Str::contains(request()->url(), 'settings'),
                   'text-gray-300 hover:text-white' => !Str::contains(
                       request()->url(),
                       'settings'
                   ),
               ])>
                <span x-cloak
                    @class([
                        'rounded p-1',
                        'bg-blue-600' => Str::contains(request()->url(), 'settings'),
                    ])>
                    {!! Lunar\Hub\LunarHub::icon('cog', 'w-5 h-5') !!}
                </span>

                <span>
                    {{ __('adminhub::global.settings') }}
                </span>
            </a>
        </div>
    @endif
</div>
