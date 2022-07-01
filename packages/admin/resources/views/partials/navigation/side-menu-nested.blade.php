<x-hub::layout.side-menu>
    <x-hub::menu handle="{{ $menu }}"
                 current="{{ request()->route()->getName() }}">
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
    </x-hub::menu>
</x-hub::layout.side-menu>
