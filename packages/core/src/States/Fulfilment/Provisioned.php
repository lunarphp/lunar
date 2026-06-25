<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Enums\FulfilmentStateCategory;

/**
 * A digital fulfilment has been provisioned — the licence key sent, the access
 * granted, the voucher issued. The `digital` method's `fulfilledState()`.
 */
class Provisioned extends FulfilmentState
{
    public static string $name = 'provisioned';

    public function label(): string
    {
        return __('lunar::states.fulfilment.provisioned');
    }

    public function category(): FulfilmentStateCategory
    {
        return FulfilmentStateCategory::Fulfilled;
    }
}
