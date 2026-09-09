<x-mail::message>
# {{ __('lunar::notifications.refund_issued.heading', ['reference' => $reference]) }}

@include('lunar::mail.partials.greeting', ['order' => $order])

{{ __('lunar::notifications.refund_issued.intro', ['reference' => $reference]) }}

@if (filled($amount))
**{{ __('lunar::notifications.partials.refund_amount') }}:** {{ $amount }}

@endif
@if (filled($message))
{{ $message }}

@endif
{{ __('lunar::notifications.refund_issued.outro') }}

@include('lunar::mail.partials.signoff')
</x-mail::message>
