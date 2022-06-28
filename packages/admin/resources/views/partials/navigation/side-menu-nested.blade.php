<x-hub::side-menu-layout>
    <x-hub::menu handle="{{ $menu }}"
                 current="{{ request()->route()->getName() }}">
        <ul class="space-y-2">
            @foreach ($component->items as $item)
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
    </x-hub::menu>
</x-hub::side-menu-layout>
