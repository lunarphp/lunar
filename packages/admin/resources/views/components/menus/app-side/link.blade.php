<div
    @class([
        'group flex',
        'justify-between items-center' => $hasSubItems
    ])
>
    <a href="{{ route($item->route) }}"
       @class([
           'items-center flex space-x-2 text-sm rounded',
           'text-white font-semibold' => $active,
           'text-gray-300 hover:text-white' => !$active,
       ])
    >
        <span x-cloak
              @class([
                'rounded p-1',
                'bg-blue-600' => $active,
              ])>
            {!! $item->renderIcon('w-5 h-5') !!}
        </span>

        <span>
            {{ $item->name }}
        </span>
    </a>


    @if ($hasSubItems)
        <button x-cloak
                x-show="!menuCollapsed"
                x-on:click.prevent="showSubMenu = !showSubMenu"
                class="text-gray-300 hover:text-white bg-gray-700 rounded border border-gray-800 p-1">
            <span :class="{ '-rotate-90': showSubMenu }"
                  class="block transition">
                <x-hub::icon ref="chevron-left"
                             class="w-3 h-3" />
            </span>
        </button>
    @endif
</div>
