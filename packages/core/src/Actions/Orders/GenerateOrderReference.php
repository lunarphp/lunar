<?php

namespace Lunar\Actions\Orders;

use Lunar\Models\Contracts\Order as OrderContract;

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
