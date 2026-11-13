<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Location;

interface ChangesFulfilmentMethod
{
    /**
     * @param  array<int|string, int>|null  $moves  [order_line_id => quantity]; null moves everything
     */
    public function execute(Fulfilment $fulfilment, string $method, ?Location $location = null, ?array $moves = null): Fulfilment;
}
