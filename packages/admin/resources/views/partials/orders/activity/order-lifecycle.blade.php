@switch($log->event)
    @case('order-closed')
        {{ __('lunarpanel::components.activity-log.partials.orders.order_closed') }}
        @break

    @case('order-reopened')
        {{ __('lunarpanel::components.activity-log.partials.orders.order_reopened') }}
        @break

    @case('order-cancelled')
        @php($reason = $log->getExtraProperty('reason'))
        @if ($reason)
            {{ __('lunarpanel::components.activity-log.partials.orders.order_cancelled', [
                'reason' => \Lunar\Core\Facades\CancelReasons::label($reason),
            ]) }}
        @else
            {{ __('lunarpanel::components.activity-log.partials.orders.order_cancelled_no_reason') }}
        @endif
        @break
@endswitch
