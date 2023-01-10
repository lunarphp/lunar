<div class="overflow-y-auto h-full bg-white">
    <div class="border-t border-b border-gray-100 pt-4 p-4">
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
        <div class="p-4 border-t border-gray-100 bottom-0">
            <a href="{{ route('hub.settings') }}"
               @class([
                   'flex items-center gap-2 p-2 rounded w-full text-sm',
                   'bg-blue-50 text-blue-700' => Str::contains(request()->url(), 'settings'),
                   'text-gray-500 hover:text-gray-900' => !Str::contains(
                       request()->url(),
                       'settings'
                   ),
               ])>
                <span x-cloak>
                    {!! Lunar\Hub\LunarHub::icon('cog', 'w-5 h-5') !!}
                </span>

                <span>
                    {{ __('adminhub::global.settings') }}
                </span>
            </a>
        </div>
    @endif
</div>
