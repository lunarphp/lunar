<?php

namespace Lunar\Core\States\Fulfilment;

class Returned extends FulfilmentState
{
    public static string $name = 'returned';

    public function label(): string
    {
        return __('lunar::states.fulfilment.returned');
    }
}
