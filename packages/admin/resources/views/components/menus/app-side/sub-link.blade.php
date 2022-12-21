<a href="{{ route($item->route) }}"
   @class([
       'flex text-sm font-medium border-l py-1 ml-[14px] pl-[21px]',
       'text-white border-blue-600' => $active,
       'text-gray-400 hover:text-gray-300 border-gray-600' => !$active,
   ])>
    {{ $item->name }}
</a>
