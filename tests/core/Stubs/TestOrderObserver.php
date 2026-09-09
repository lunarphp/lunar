<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Models\Contracts\Order;

class TestOrderObserver
{
    public static int $created = 0;

    public function created(Order $order): void
    {
        static::$created++;
    }
}
