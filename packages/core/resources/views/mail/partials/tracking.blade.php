<x-mail::table>
| {{ __('lunar::notifications.partials.carrier') }} | {{ __('lunar::notifications.partials.tracking_number') }} |
|:--|:--|
@foreach ($trackings as $tracking)
| {{ \Lunar\Core\Notifications\MailTable::cell($tracking->carrier()?->getName() ?? $tracking->carrier) }}@if (filled($tracking->shippingMethodLabel())) ({{ \Lunar\Core\Notifications\MailTable::cell($tracking->shippingMethodLabel()) }})@endif | @if (filled($tracking->url))[{{ \Lunar\Core\Notifications\MailTable::cell($tracking->tracking_number ?? __('lunar::notifications.partials.track')) }}]({{ $tracking->url }})@else{{ \Lunar\Core\Notifications\MailTable::cell($tracking->tracking_number) }}@endif |
@endforeach
</x-mail::table>
