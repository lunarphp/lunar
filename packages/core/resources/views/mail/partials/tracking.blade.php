<x-mail::table>
| {{ __('lunar::notifications.partials.carrier') }} | {{ __('lunar::notifications.partials.tracking_number') }} |
|:--|:--|
@foreach ($trackings as $tracking)
| {{ $tracking->carrier()?->getName() ?? $tracking->carrier }}@if (filled($tracking->shippingMethodLabel())) ({{ $tracking->shippingMethodLabel() }})@endif | @if (filled($tracking->url))[{{ $tracking->tracking_number ?? __('lunar::notifications.partials.track') }}]({{ $tracking->url }})@else{{ $tracking->tracking_number }}@endif |
@endforeach
</x-mail::table>
