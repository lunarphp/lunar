<?php

namespace Lunar\Core\States\Order\Fulfilment;

class Returned extends FulfilmentStatus
{
    public static string $name = 'returned';

    public function label(): string
    {
        return __('lunar::states.fulfilment-status.returned');
    }
}
