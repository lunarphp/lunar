<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

class Returned extends FulfilmentState
{
    public static string $name = 'returned';

    public function label(): string
    {
        return __('lunar::states.fulfilment.returned');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Returned;
    }
}
