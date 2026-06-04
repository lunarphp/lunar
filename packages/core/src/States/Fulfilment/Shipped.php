<?php

namespace Lunar\Core\States\Fulfilment;

class Shipped extends FulfilmentState
{
    public static string $name = 'shipped';

    public function label(): string
    {
        return __('lunar::states.fulfilment.shipped');
    }
}
