<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Lunar\Models\Order;

class CleanUpOrderLines
{
    /**
     * @return mixed
     */
    public function handle(Order $order, Closure $next)
    {
        $cart = $order->cart;

        $purchasableIds = $cart->lines->pluck('purchasable_id');

        $order->productLines()
            ->whereNotIn('purchasable_id', $purchasableIds)
            ->delete();

        return $next($order);
    }
}
