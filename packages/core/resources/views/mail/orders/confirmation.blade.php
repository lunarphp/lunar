<x-mail::message>
# {{ __('lunar::notifications.order_confirmation.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.order_confirmation.intro', ['reference' => $reference, 'date' => $placedAt]) }}

@if (filled($message))
{{ $message }}

@endif
@include('lunar::mail.partials.order-summary', ['order' => $order, 'lines' => $lines])

@if ($order->shippingAddress)
**{{ __('lunar::notifications.partials.shipping_address') }}**

@include('lunar::mail.partials.address', ['address' => $order->shippingAddress])

@endif
@if ($order->billingAddress)
**{{ __('lunar::notifications.partials.billing_address') }}**

@include('lunar::mail.partials.address', ['address' => $order->billingAddress])

@endif
**{{ __('lunar::notifications.partials.payment_status') }}:** {{ $order->payment_status?->label() }}

{{ __('lunar::notifications.order_confirmation.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
