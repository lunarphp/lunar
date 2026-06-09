@php
    $id = $log->getExtraProperty('reference') ?: $log->getExtraProperty('fulfilment_id');
    $type = $log->getExtraProperty('type');
@endphp

@switch($type)
    @case('state')
        {{ __('lunarpanel::components.activity-log.partials.orders.fulfilment_state', [
            'id' => $id,
            'state' => __('lunar::states.fulfilment.'.$log->getExtraProperty('to')),
        ]) }}
        @break

    @case('held')
        @php($reason = $log->getExtraProperty('reason'))
        @if ($reason)
            {{ __('lunarpanel::components.activity-log.partials.orders.fulfilment_held', [
                'id' => $id,
                'reason' => config('lunar.fulfilment.hold_reasons.'.$reason, $reason),
            ]) }}
        @else
            {{ __('lunarpanel::components.activity-log.partials.orders.fulfilment_held_no_reason', ['id' => $id]) }}
        @endif
        @break

    @case('released')
        {{ __('lunarpanel::components.activity-log.partials.orders.fulfilment_released', ['id' => $id]) }}
        @break
@endswitch
