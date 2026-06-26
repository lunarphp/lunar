<?php

namespace Lunar\Core\States\Order\Fulfilment;

class PartiallyFulfilled extends FulfilmentStatus
{
    public static string $name = 'partially-fulfilled';

    public function label(): string
    {
        return __('lunar::states.fulfilment-status.partially-fulfilled');
    }
}
