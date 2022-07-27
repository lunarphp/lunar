<x-hub::layout.side-menu>
    <x-hub::menu handle="{{ $menu }}"
                 current="{{ request()->route()->getName() }}">
        <x-hub::menu-list
                type="sub_menu"
                :sections="$component->sections"
                :items="$component->items"
                :active="$component->attributes->get('current')" />
    </x-hub::menu>
</x-hub::layout.side-menu>
