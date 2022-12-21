<div>
    <a href="{{ route($item->route) }}"
       @class([
           'flex items-center space-x-2 text-sm rounded',
           'text-white font-semibold' => $active,
           'text-gray-300 hover:text-white' => !$active,
       ])>
        <span x-cloak
              @class([
                'rounded p-1',
                'bg-blue-600' => $active,
              ])
              :class="{ 'mx-auto': !showExpandedMenu }">
            {!! $item->renderIcon('w-5 h-5') !!}
        </span>

        <span x-cloak
              x-show="showExpandedMenu"
              :class="{
                  'absolute left-[calc(100%_+_4px)] m-auto bg-black z-50 text-white rounded py-1.5 px-3 group-hover:!block':
                      !showExpandedMenu,
              }">
            {{ $item->name }}
        </span>
    </a>
</div>
