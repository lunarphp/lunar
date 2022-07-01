<x-hub::layout.side-menu>
    <x-hub::menu handle="{{ $menu }}"
                 current="{{ request()->route()->getName() }}">
        <ul class="space-y-2">
            @foreach ($component->items as $item)
                <li>
                    <a href="{{ route($item->route) }}"
                       @class([
                           'relative flex items-center gap-2 p-2 rounded text-gray-500 dark:text-gray-400',
                           'bg-blue-50 text-blue-700 hover:text-blue-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:text-white' => $item->isActive(
                               $component->attributes->get('current')
                           ),
                           'hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-gray-800 dark:hover:text-white' => !$item->isActive(
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
