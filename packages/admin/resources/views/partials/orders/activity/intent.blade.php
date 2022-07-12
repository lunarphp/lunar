{{ __('adminhub::components.activity-log.orders.authorized', [
    'amount' => price($log->getExtraProperty('amount'), $log->subject->currency)->formatted,
    'last_four' => $log->getExtraProperty('last_four'),
]) }}
