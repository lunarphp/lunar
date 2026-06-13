<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

/**
 * The customer has picked up the collection parcel — the collection flow's
 * handed-over terminal (the `collection` method's `fulfilledState()`).
 */
class Collected extends FulfilmentState
{
    public static string $name = 'collected';

    public function label(): string
    {
        return __('lunar::states.fulfilment.collected');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Fulfilled;
    }
}
