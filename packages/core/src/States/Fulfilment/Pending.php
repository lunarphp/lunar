<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

class Pending extends FulfilmentState
{
    public static string $name = 'pending';

    public function label(): string
    {
        return __('lunar::states.fulfilment.pending');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Outstanding;
    }
}
