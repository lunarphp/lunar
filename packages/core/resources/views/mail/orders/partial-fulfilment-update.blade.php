<x-mail::message>
# {{ __('lunar::notifications.partial_fulfilment_update.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.partial_fulfilment_update.intro', ['reference' => $reference]) }}

@if (filled($message))
{{ $message }}

@endif
@if (count($lines) > 0)
{{ __('lunar::notifications.partial_fulfilment_update.outstanding') }}

@include('lunar::mail.partials.fulfilment-lines', ['lines' => $lines])

@endif
{{ __('lunar::notifications.partial_fulfilment_update.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
