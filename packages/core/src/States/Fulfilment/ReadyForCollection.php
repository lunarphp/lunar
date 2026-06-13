<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

/**
 * A collection parcel is picked, packed and waiting at the counter for the
 * customer to pick up — the collection flow's outstanding "in-progress" step.
 */
class ReadyForCollection extends FulfilmentState
{
    public static string $name = 'ready-for-collection';

    public function label(): string
    {
        return __('lunar::states.fulfilment.ready-for-collection');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Outstanding;
    }
}
