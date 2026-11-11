<x-mail::message>
# {{ __('lunar::notifications.order_shipped.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.order_shipped.intro', ['reference' => $reference]) }}

@if (filled($message))
{{ $message }}

@endif
@include('lunar::mail.partials.fulfilment-lines', ['lines' => $lines])

@if ($trackings->isNotEmpty())
{{ __('lunar::notifications.order_shipped.tracking') }}

@include('lunar::mail.partials.tracking', ['trackings' => $trackings])

@endif
{{ __('lunar::notifications.order_shipped.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
