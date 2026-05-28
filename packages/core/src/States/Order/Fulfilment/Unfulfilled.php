<?php

namespace Lunar\Core\States\Order\Fulfilment;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\FulfilmentState;

class Unfulfilled extends FulfilmentState
{
    public static string $name = 'unfulfilled';

    public function label(): string
    {
        return __('lunar::states.fulfilment.unfulfilled');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Pending;
    }
}
