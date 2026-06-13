<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

class Cancelled extends FulfilmentState
{
    public static string $name = 'cancelled';

    public function label(): string
    {
        return __('lunar::states.fulfilment.cancelled');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Cancelled;
    }
}
