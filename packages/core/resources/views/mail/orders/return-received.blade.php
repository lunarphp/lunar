<x-mail::message>
# {{ __('lunar::notifications.return_received.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.return_received.intro', ['reference' => $reference]) }}

@if (filled($message))
{{ $message }}

@endif
@include('lunar::mail.partials.fulfilment-lines', ['lines' => $lines])

{{ __('lunar::notifications.return_received.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
