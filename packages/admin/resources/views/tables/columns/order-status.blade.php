<div
{{ $attributes->merge($getExtraAttributes())->class([
    'px-4 py-3 filament-tables-text-column',
    'whitespace-normal' => $canWrap(),
]) }}
>
    <x-hub::orders.status :status="$getState()" />
</div>
