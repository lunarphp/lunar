<x-mail::message>
# {{ __('lunar::notifications.order_ready_for_collection.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.order_ready_for_collection.intro', ['reference' => $reference]) }}

@if (filled($message))
{{ $message }}

@endif
@if (filled($location))
**{{ __('lunar::notifications.partials.collect_from') }}:** {{ $location }}

@endif
@include('lunar::mail.partials.fulfilment-lines', ['lines' => $lines])

{{ __('lunar::notifications.order_ready_for_collection.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
