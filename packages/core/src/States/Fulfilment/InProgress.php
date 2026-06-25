<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

class InProgress extends FulfilmentState
{
    public static string $name = 'in-progress';

    public function label(): string
    {
        return __('lunar::states.fulfilment.in-progress');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Outstanding;
    }
}
