{{ __('lunarpanel::components.activity-log.partials.orders.authorized', [
    'amount' => price($log->getExtraProperty('amount'), $log->subject->currency)->formatted,
    'last_four' => $log->getExtraProperty('last_four'),
]) }}
