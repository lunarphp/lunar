<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

interface MovesFulfilmentLines
{
    /**
     * Move the given line quantities out of one pre-ship fulfilment and into
     * another on the same order. The source is removed if it is left empty.
     *
     * @param  array<int|string, int>  $moves  [order_line_id => quantity to move]
     * @return Fulfilment the destination fulfilment
     */
    public function execute(FulfilmentContract $from, FulfilmentContract $to, array $moves): Fulfilment;
}
