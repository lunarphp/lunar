<div class="space-y-2">
    @if (count($items))
        <ul class="space-y-2">
            @foreach ($items as $item)
                <li>
                    <x-hub::menus.app-side.link
                        :item="$$item"
                        :active="$item->isActive($active)"
                    />
                </li>
            @endforeach
        </ul>
    @endif

    @forelse ($sections as $section)
        @if (count($section->getItems()))
            <div>
                <header class="text-sm font-semibold text-gray-600">
                    {{ $section->name }}
                </header>

                <ul class="mt-1 space-y-0.5">
                    @foreach ($section->getItems() as $item)
                        <li>
                            <x-hub::menus.app-side.link
                                :item="$item"
                                :active="$item->isActive($active)"
                            />
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @empty
    @endforelse
</div>
