@php($name = $order->billingAddress?->first_name ?? $order->shippingAddress?->first_name)
{{ filled($name) ? __('lunar::notifications.partials.greeting', ['name' => $name]) : __('lunar::notifications.partials.greeting_fallback') }}
