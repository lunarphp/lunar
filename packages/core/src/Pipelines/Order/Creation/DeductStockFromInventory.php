<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;

class DeductStockFromInventory
{
    /**
     * @return Closure
     */
    public function handle(Order $order, Closure $next)
    {
        $order->lines->each(function (OrderLine $line) {
            $line->purchasable->deductStock($line->quantity);
        });

        return $next($order->refresh());
    }
}
