<?php

namespace GetCandy\Actions\Orders;

use GetCandy\Base\Addressable;
use GetCandy\DataTypes\Price;
use GetCandy\Facades\Pricing;
use GetCandy\Facades\Taxes;
use GetCandy\Models\CartLine;
use GetCandy\Models\Order;
use Illuminate\Support\Collection;

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

        if (!$generator) {
            return null;
        }

        return app($generator)->generate($order);
    }
}
