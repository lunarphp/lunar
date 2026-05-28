<?php

namespace Lunar\Core\States\Order\Fulfilment;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\FulfilmentState;

class Returned extends FulfilmentState
{
    public static string $name = 'returned';

    public function label(): string
    {
        return __('lunar::states.fulfilment.returned');
    }

    public function category(): StateCategory
    {
        return StateCategory::Failed;
    }
}
