<?php

namespace Lunar\Core\States\Order\Fulfilment;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\FulfilmentState;

class Shipped extends FulfilmentState
{
    public static string $name = 'shipped';

    public function label(): string
    {
        return __('lunar::states.fulfilment.shipped');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Active;
    }
}
