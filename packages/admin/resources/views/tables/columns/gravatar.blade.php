<div
  {{ $attributes->merge($getExtraAttributes())->class([
      'px-6 py-4 whitespace-no-wrap leading-5 text-cool-gray-900 filament-tables-text-column',
      'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500' => $getAction() || $getUrl(),
      'whitespace-normal' => $canWrap(),
  ]) }}
>
  <x-hub::gravatar email="{{ $getState() }}" class="w-6 h-6 rounded-full" />
</div>
