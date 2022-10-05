<?php

namespace Lunar\Actions\Orders;

use Lunar\Models\Order;

class GenerateOrderReference
{
    /**
     * Execute the action.
     *
     * @param  \Lunar\Models\CartLine  $cartLine
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     * @return \Lunar\Models\CartLine
     */
    public function execute(
        Order $order
    ) {
        $generator = config('lunar.orders.reference_generator');

        if (! $generator) {
            return null;
        }

        return app($generator)->generate($order);
    }
}
