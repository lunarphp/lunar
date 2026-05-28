<?php

namespace Lunar\Core\States\Order\Fulfilment;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\FulfilmentState;

class Backordered extends FulfilmentState
{
    public static string $name = 'backordered';

    public function label(): string
    {
        return __('lunar::states.fulfilment.backordered');
    }

    public function category(): StateCategory
    {
        return StateCategory::Blocked;
    }
}
