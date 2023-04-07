<div
    @class([
        'group flex space-x-2',
        'justify-between items-center' => $hasSubItems
    ])
>
    <a href="{{ route($item->route) }}"
       @class([
           'flex items-center gap-2 p-2 rounded w-full text-sm font-medium',
           'bg-sky-100 text-sky-800' => $active,
           'text-sky-800 hover:text-gray-950' => !$active,
       ])
    >
        <span x-cloak>
            {!! $item->renderIcon('w-5 h-5') !!}
        </span>

        <span>
            {{ $item->name }}
        </span>
    </a>


    @if ($hasSubItems)
        <button x-cloak
                x-on:click.prevent="showSubMenu = !showSubMenu"
                class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 bg-white rounded p-1">
            <span :class="{ '-rotate-90': showSubMenu }"
                  class="block transition">
                <x-hub::icon ref="chevron-left"
                             class="w-3 h-3" />
            </span>
        </button>
    @endif
</div>
