<?php

namespace Lunar\Core\States\Fulfilment;

class Cancelled extends FulfilmentState
{
    public static string $name = 'cancelled';

    public function label(): string
    {
        return __('lunar::states.fulfilment.cancelled');
    }
}
