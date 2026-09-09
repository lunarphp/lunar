<x-mail::message>
# {{ __('lunar::notifications.order_cancelled.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.order_cancelled.intro', ['reference' => $reference]) }}

@if (filled($reason))
**{{ __('lunar::notifications.partials.reason') }}:** {{ $reason }}

@endif
@if (filled($message))
{{ $message }}

@endif
{{ __('lunar::notifications.order_cancelled.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
