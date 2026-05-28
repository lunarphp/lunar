<?php

namespace Lunar\Core\States\Order\Fulfilment;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\FulfilmentState;

class PartiallyShipped extends FulfilmentState
{
    public static string $name = 'partially-shipped';

    public function label(): string
    {
        return __('lunar::states.fulfilment.partially-shipped');
    }

    public function category(): StateCategory
    {
        return StateCategory::Active;
    }
}
