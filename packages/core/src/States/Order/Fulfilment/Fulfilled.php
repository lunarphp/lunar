<?php

namespace Lunar\Core\States\Order\Fulfilment;

class Fulfilled extends FulfilmentStatus
{
    public static string $name = 'fulfilled';

    public function label(): string
    {
        return __('lunar::states.fulfilment-status.fulfilled');
    }
}
