<x-hub::layout.side-menu>
    <x-hub::menu handle="{{ $menu }}"
                 current="{{ request()->route()->getName() }}">
        <div class="space-y-4">
            @forelse ($component->sections as $section)
                <div>
                    <header class="text-sm font-semibold tracking-wide text-gray-500">
                        {{ $section->name }}
                    </header>

                    <ul class="mt-2 space-y-2">
                        @foreach ($section->getItems() as $item)
                            <li>
                                <a href="{{ route($item->route) }}"
                                   @class([
                                       'flex items-center gap-2 p-2 rounded text-gray-500',
                                       'bg-blue-50 text-blue-700 hover:text-blue-600' => $item->isActive(
                                           $component->attributes->get('current')
                                       ),
                                       'hover:bg-blue-50 hover:text-blue-700' => !$item->isActive(
                                           $component->attributes->get('current')
                                       ),
                                   ])>
                                    {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                                    <span class="text-sm font-medium">
                                        {{ $item->name }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
            @endforelse

            @if (count($component->items))
                <div class="pt-4 border-t border-gray-100">
                    <ul class="space-y-2">
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
                                    {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                                    <span class="text-sm font-medium">
                                        {{ $item->name }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </x-hub::menu>
</x-hub::layout.side-menu>
