<?php

namespace Lunar\Core\States\Order\Fulfilment;

class PartiallyReturned extends FulfilmentStatus
{
    public static string $name = 'partially-returned';

    public function label(): string
    {
        return __('lunar::states.fulfilment-status.partially-returned');
    }
}
