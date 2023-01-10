<div
    @class([
        'group flex space-x-2',
        'justify-between items-center' => $hasSubItems
    ])
>
    <a href="{{ route($item->route) }}"
       @class([
           'flex items-center gap-2 p-2 rounded w-full text-sm font-medium',
           'bg-blue-50 text-blue-700' => $active,
           'text-gray-500 hover:text-gray-900' => !$active,
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
                class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 bg-white rounded border border-gray-200 p-1">
            <span :class="{ '-rotate-90': showSubMenu }"
                  class="block transition">
                <x-hub::icon ref="chevron-left"
                             class="w-3 h-3" />
            </span>
        </button>
    @endif
</div>
