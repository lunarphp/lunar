<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;

interface CreatesFulfilment
{
    /**
     * Create a fulfilment covering the given order lines.
     *
     * @param  array<int|string, int>  $lines  [order_line_id => quantity]
     * @param  array<string, mixed>  $attributes  method / reference / location_id / notes / meta
     */
    public function execute(Order $order, array $lines, array $attributes = []): Fulfilment;
}
