<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

class Shipped extends FulfilmentState
{
    public static string $name = 'shipped';

    public function label(): string
    {
        return __('lunar::states.fulfilment.shipped');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Fulfilled;
    }
}
