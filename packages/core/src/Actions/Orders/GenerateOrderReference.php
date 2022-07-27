<?php

namespace GetCandy\Actions\Orders;

use GetCandy\Models\Order;

class GenerateOrderReference
{
    /**
     * Execute the action.
     *
     * @param  \GetCandy\Models\CartLine  $cartLine
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     * @return \GetCandy\Models\CartLine
     */
    public function execute(
        Order $order
    ) {
        $generator = config('getcandy.orders.reference_generator');

        if (! $generator) {
            return null;
        }

        return app($generator)->generate($order);
    }
}
