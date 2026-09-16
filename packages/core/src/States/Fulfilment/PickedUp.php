<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

/**
 * The customer has picked up the fulfilment — the pickup flow's handed-over
 * terminal (the `pickup` method's `fulfilledState()`).
 */
class PickedUp extends FulfilmentState
{
    public static string $name = 'picked-up';

    public function label(): string
    {
        return __('lunar::states.fulfilment.picked-up');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Fulfilled;
    }
}
