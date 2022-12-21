<div>
    <x-hub::menus.app-side.link
        :item="$section"
        :active="$section->isActive($current) || $section->hasActive($current)"
    />
    @if (count($section->getItems()))
        <nav class="mt-2">
            @foreach ($section->getItems() as $item)
                <x-hub::menus.app-side.sub-link :item="$item" :active="$item->isActive($current)" />
            @endforeach
        <nav>
    @endif
</div>
