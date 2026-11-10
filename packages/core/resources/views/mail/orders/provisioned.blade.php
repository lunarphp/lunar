<x-mail::message>
# {{ __('lunar::notifications.order_provisioned.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.order_provisioned.intro', ['reference' => $reference]) }}

@if (filled($message))
{{ $message }}

@endif
@include('lunar::mail.partials.fulfilment-lines', ['lines' => $lines])

{{ __('lunar::notifications.order_provisioned.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
