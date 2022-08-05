@php
    $description = $getDescription();
    $descriptionPosition = $getDescriptionPosition();
@endphp

<div
    {{ $attributes->merge($getExtraAttributes())->class([
        'px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-900 filament-tables-text-column',
        'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500' => $getAction() || $getUrl(),
        'whitespace-normal' => $canWrap(),
    ]) }}
>
    @if (filled($description) && $descriptionPosition === 'above')
        <span class="block text-sm text-gray-400">
            {!! \Illuminate\Support\Str::of($description)->markdown()->sanitizeHtml() !!}
        </span>
    @endif

    {{ $getFormattedState() }}

    @if (filled($description) && $descriptionPosition === 'below')
        <span class="block text-sm text-gray-400">
            {!! \Illuminate\Support\Str::of($description)->markdown()->sanitizeHtml() !!}
        </span>
    @endif
</div>
