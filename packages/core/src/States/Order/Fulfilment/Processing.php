<?php

namespace Lunar\Core\States\Order\Fulfilment;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\FulfilmentState;

class Processing extends FulfilmentState
{
    public static string $name = 'processing';

    public function label(): string
    {
        return __('lunar::states.fulfilment.processing');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Pending;
    }
}
