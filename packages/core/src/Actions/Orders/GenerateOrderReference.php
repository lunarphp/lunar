<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\GeneratesOrderReference;
use Lunar\Core\Models\Order;

class GenerateOrderReference implements GeneratesOrderReference
{
    /**
     * Execute the action.
     */
    public function execute(Order $order): ?string
    {
        $generator = config('lunar.orders.reference_generator');

        if (! $generator) {
            return null;
        }

        return app($generator)->generate($order);
    }
}
