<div
    {{ $attributes->merge($getExtraAttributes())->class([
        'px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-900 filament-tables-text-column',
        'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500' => $getAction() || $getUrl(),
        'whitespace-normal' => $canWrap(),
    ]) }}
>
    @php
        $skus = $getState()
    @endphp
    @if ($skus->count() > 2)
        <x-hub::tooltip text="{{ $skus->join(', ') }}"
                        left>
            Multiple
        </x-hub::tooltip>
    @else
        {{ $skus->join(', ') }}
    @endif
</div>
