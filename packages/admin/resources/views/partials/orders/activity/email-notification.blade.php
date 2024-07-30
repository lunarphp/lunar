<div>
    Sent <strong>{{ $log->getExtraProperty('mailer') ?: 'Email notification' }} </strong> to {{ $log->getExtraProperty('email') }}
    {{-- preview is not ported --}}
</div>
