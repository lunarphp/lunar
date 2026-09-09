@php($parts = array_filter([
    trim(($address->first_name ?? '').' '.($address->last_name ?? '')),
    $address->company_name,
    $address->line_one,
    $address->line_two,
    $address->line_three,
    $address->city,
    $address->state,
    $address->postcode,
    $address->country?->name,
], 'filled'))
{!! implode("  \n", array_map('e', $parts)) !!}
