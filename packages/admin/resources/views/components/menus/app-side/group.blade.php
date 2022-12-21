<div class="mt-4 space-y-2">
    <header
        x-cloak
        x-show="showExpandedMenu"
        class="text-sm font-semibold text-gray-400"
    >
        {{ $group->name }}
    </header>

    @if (count($group->getItems()))
        @foreach ($group->getItems() as $item)
            <x-hub::menus.app-side.link
                :item="$item"
                :active="$item->isActive(
                    $current
                )"
            />
        @endforeach
    @endif

    @if (count($group->getSections()))
        @foreach ($group->getSections() as $section)
            <x-hub::menus.app-side.section :section="$section" :current="$current" />
        @endforeach
    @endif
</div>
