{{ __('adminhub::components.activity-log.orders.capture', [
    'amount' => price($activity->getExtraProperty('amount'), $this->order->currency)->formatted,
    'last_four' => $activity->getExtraProperty('last_four'),
]) }}
