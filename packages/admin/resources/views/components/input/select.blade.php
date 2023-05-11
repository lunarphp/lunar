<select
  {{ $attributes }}
  {{-- class="" --}}
  @class([
    'form-select block w-full py-2 pl-3 pr-10 text-base border-gray-300 rounded-md focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm',
    'border-red-600' => !!$error
  ])
>
  {{ $slot }}
</select>
