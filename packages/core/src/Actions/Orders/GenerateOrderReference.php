<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;

class GenerateOrderReference
{
    /**
     * Execute the action.
     *
     * @return string
     */
    public function execute(OrderContract $order)
    {
        $generator = config('lunar.orders.reference_generator');

        if (! $generator) {
            return null;
        }

        return app($generator)->generate($order);
    }
}
