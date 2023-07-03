<?php

namespace Lunar\Base\Traits;

use Lunar\Facades\Stock;

trait HasStock
{
    public function availableStock()
    {
        return Stock::availableStock($this);
    }
}
