<a href="{{ route($item->route) }}"
   @class([
       'flex text-sm border-l py-1 ml-[14px] pl-[21px]',
       'text-blue-700 border-blue-600' => $active,
       'text-gray-500 hover:text-gray-800 border-gray-200' => !$active,
   ])>
    {{ $item->name }}
</a>
