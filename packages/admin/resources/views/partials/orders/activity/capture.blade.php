{{ __('lunarpanel::components.activity-log.partials.orders.capture', [
    'amount' => price($log->getExtraProperty('amount'), $log->subject->currency)->formatted,
    'last_four' => $log->getExtraProperty('last_four'),
]) }}
