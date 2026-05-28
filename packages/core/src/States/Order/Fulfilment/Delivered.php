<?php

namespace Lunar\Core\States\Order\Fulfilment;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\FulfilmentState;

class Delivered extends FulfilmentState
{
    public static string $name = 'delivered';

    public function label(): string
    {
        return __('lunar::states.fulfilment.delivered');
    }

    public function category(): StateCategory
    {
        return StateCategory::Complete;
    }
}
