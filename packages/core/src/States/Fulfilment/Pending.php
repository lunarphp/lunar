<?php

namespace Lunar\Core\States\Fulfilment;

class Pending extends FulfilmentState
{
    public static string $name = 'pending';

    public function label(): string
    {
        return __('lunar::states.fulfilment.pending');
    }
}
