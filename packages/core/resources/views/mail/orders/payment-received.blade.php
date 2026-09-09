<x-mail::message>
# {{ __('lunar::notifications.payment_received.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.payment_received.intro', ['reference' => $reference]) }}

@if (filled($message))
{{ $message }}

@endif
**{{ __('lunar::notifications.partials.order_total') }}:** {{ $order->format('total') }}

{{ __('lunar::notifications.payment_received.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
