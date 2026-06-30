<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;

interface SplitsFulfilment
{
    /**
     * Move outstanding quantities out of a pre-ship fulfilment into a new one.
     *
     * @param  array<int|string, int>  $moves  [order_line_id => quantity to move out]
     * @return Fulfilment the new fulfilment carrying the moved quantities
     */
    public function execute(Fulfilment $fulfilment, array $moves): Fulfilment;
}
