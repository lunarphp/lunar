<?php

namespace Lunar\Core\States\Fulfilment;

class InProgress extends FulfilmentState
{
    public static string $name = 'in-progress';

    public function label(): string
    {
        return __('lunar::states.fulfilment.in-progress');
    }
}
