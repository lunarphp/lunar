{{ __('adminhub::components.activity-log.orders.refund', [
    'amount' => price($activity->getExtraProperty('amount'), $log->subject->currency)->formatted,
    'last_four' => $log->getExtraProperty('last_four'),
]) }}
@if($notes = $log->getExtraProperty('notes'))
<p class="mt-2 text-sm text-gray-600">{{ nl2br($notes) }}</p>
@endif
