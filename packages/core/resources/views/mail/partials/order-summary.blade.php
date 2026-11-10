<x-mail::table>
| {{ __('lunar::notifications.partials.item') }} | {{ __('lunar::notifications.partials.quantity') }} | {{ __('lunar::notifications.partials.total') }} |
|:--|:--:|--:|
@foreach ($lines as $line)
| {{ \Lunar\Core\Notifications\MailTable::cell($line->description) }}@if (filled($line->option)) ({{ \Lunar\Core\Notifications\MailTable::cell($line->option) }})@endif | {{ $line->quantity }} | {{ $line->format('total') }} |
@endforeach
| **{{ __('lunar::notifications.partials.sub_total') }}** | | {{ $order->format('sub_total') }} |
@if ($order->discount_total > 0)
| **{{ __('lunar::notifications.partials.discount') }}** | | -{{ $order->format('discount_total') }} |
@endif
| **{{ __('lunar::notifications.partials.shipping') }}** | | {{ $order->format('shipping_total') }} |
| **{{ __('lunar::notifications.partials.tax') }}** | | {{ $order->format('tax_total') }} |
| **{{ __('lunar::notifications.partials.total') }}** | | **{{ $order->format('total') }}** |
</x-mail::table>
