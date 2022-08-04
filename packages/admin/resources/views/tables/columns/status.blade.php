<div
{{ $attributes->merge($getExtraAttributes())->class([
    'px-4 py-3 filament-tables-text-column',
    'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500' => $getAction() || $getUrl(),
    'whitespace-normal' => $canWrap(),
]) }}
>
  <span @class([
      'text-xs inline-block py-1 px-2 rounded',
      'text-green-600 bg-green-50' => $getState() == 'published',
      'text-yellow-600 bg-yellow-50' => $getState() == 'draft',
      'text-red-600 bg-red-50' => $getState() == 'deleted',
  ])>
      {{ __('adminhub::components.products.index.' . $getState()) }}
  </span>
</div>
