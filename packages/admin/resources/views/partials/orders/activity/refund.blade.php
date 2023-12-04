{{ __('lunarpanel::components.activity-log.partials.orders.refund', [
    'amount' => price($log->getExtraProperty('amount'), $log->subject->currency)->formatted,
    'last_four' => $log->getExtraProperty('last_four'),
]) }}
