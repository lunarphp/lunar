<div
    x-data="{
        showSubMenu: {{ ($section->isActive($current) || $section->hasActive($current)) ? 'true' : 'false' }},
    }"
>
    <x-hub::menus.app-side.link
        :item="$section"
        :active="$section->isActive($current) || $section->hasActive($current)"
        :has-sub-items="!$section->getItems()->isEmpty()"
    />

    @if ($section->getItems()->count())
        <nav
            class="mt-2"
            x-show="showSubMenu"
            x-cloak
        >
            @foreach ($section->getItems() as $item)
                <x-hub::menus.app-side.sub-link :item="$item" :active="$item->isActive($current)" />
            @endforeach
        <nav>
    @endif
</div>
