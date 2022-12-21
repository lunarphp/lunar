<div class="overflow-y-auto h-full">
    <div class="border-b border-gray-900 sticky top-0 z-75 bg-gray-800">
        <a href="{{ route('hub.index') }}"
           class="flex items-center w-full p-4">
            <x-hub::branding.logo x-cloak
                                  x-show="showExpandedMenu" />
            <x-hub::branding.logo x-cloak
                                  x-show="!showExpandedMenu"
                                  iconOnly />
        </a>
    </div>
    <div class="border-t border-gray-700 pt-4 p-4">
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
</div>
