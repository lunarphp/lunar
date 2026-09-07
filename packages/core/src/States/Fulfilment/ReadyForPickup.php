<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

/**
 * A pickup fulfilment is picked, packed and waiting at the counter for the
 * customer to pick up — the pickup flow's outstanding "in-progress" step.
 */
class ReadyForPickup extends FulfilmentState
{
    public static string $name = 'ready-for-pickup';

    public function label(): string
    {
        return __('lunar::states.fulfilment.ready-for-pickup');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Outstanding;
    }
}
