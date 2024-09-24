<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Lunar\Actions\Orders\GenerateOrderReference;
use Lunar\Models\Currency;
use Lunar\Models\Order;

class FillOrderFromCart
{
    /**
     * @return mixed
     */
    public function handle(Order $order, Closure $next)
    {
        $cart = $order->cart->calculate();

        $order->fill([
            'user_id' => $cart->user_id,
            'customer_id' => $cart->customer_id,
            'channel_id' => $cart->channel_id,
            'status' => config('lunar.orders.draft_status'),
            'reference' => null,
            'customer_reference' => null,
            'sub_total' => $cart->subTotal->value,
            'total' => $cart->total->value,
            'discount_total' => $cart->discountTotal?->value,
            'discount_breakdown' => [],
            'shipping_total' => $cart->shippingTotal?->value ?: 0,
            'shipping_breakdown' => $cart->shippingBreakdown,
            'tax_breakdown' => $cart->taxBreakdown,
            'tax_total' => $cart->taxTotal->value,
            'currency_code' => $cart->currency->code,
            'exchange_rate' => $cart->currency->exchange_rate,
            'compare_currency_code' => Currency::getDefault()?->code,
            'meta' => $cart->meta,
        ])->save();

        $order->update([
            'reference' => app(GenerateOrderReference::class)->execute($order),
        ]);

        return $next($order);
    }
}
