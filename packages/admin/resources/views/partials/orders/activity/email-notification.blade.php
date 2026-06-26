<div class="space-y-1">
    <span>
        {{ __('lunarpanel::components.activity-log.partials.orders.email_notification', [
            'notification' => $log->getExtraProperty('notification') ?: __('lunarpanel::components.activity-log.partials.orders.email_notification_fallback'),
            'email' => $log->getExtraProperty('email'),
        ]) }}
    </span>

    @if ($message = $log->getExtraProperty('message'))
        <p class="text-xs italic">{{ $message }}</p>
    @endif
</div>
