<{{ $tag }}
  {{ $attributes->merge([
    'class' => 'block
      disabled:cursor-not-allowed disabled:opacity-50
      rounded-lg shadow-sm
      border
      inline-flex justify-center font-medium focus:outline-none focus:ring-offset-2 focus:ring-2
    ',
  ])->class([
    'py-2 px-4 text-sm' => $size == 'default',
    'py-2 px-3 leading-4 text-sm' => $size == 'sm',
    'py-1 px-2 leading-2 text-xs' => $size == 'xs',
    'border-transparent' => $theme !== 'gray',
    'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500' => $theme == 'default',
    'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500' => $theme == 'green',
    'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500' => $theme == 'danger',
    'bg-white text-gray-600 border-gray-300 hover:bg-gray-100 focus:ring-gray-400' => $theme == 'gray'
  ]) }}
>
  {{ $slot }}
</{{ $tag }}>
