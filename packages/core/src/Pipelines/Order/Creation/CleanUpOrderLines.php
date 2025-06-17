<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Order;

class CleanUpOrderLines
{
    /**
     * @param  Closure(OrderContract): mixed  $next
     */
    public function handle(OrderContract $order, Closure $next): mixed
    {
        /** @var Order $order */
        $cart = $order->cart;

        $purchasableIds = $cart->lines->pluck('purchasable_id');

        $order->productLines()
            ->whereNotIn('purchasable_id', $purchasableIds)
            ->delete();

        return $next($order);
    }
}
