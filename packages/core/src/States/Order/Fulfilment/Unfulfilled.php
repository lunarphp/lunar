<?php

namespace Lunar\Core\States\Order\Fulfilment;

class Unfulfilled extends FulfilmentStatus
{
    public static string $name = 'unfulfilled';

    public function label(): string
    {
        return __('lunar::states.fulfilment-status.unfulfilled');
    }
}
