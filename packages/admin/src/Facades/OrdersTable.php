<?php

namespace Lunar\Hub\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Hub\Base\OrdersTableInterface;

class OrdersTable extends Facade
{
    public static function getFacadeAccessor()
    {
        return OrdersTableInterface::class;
    }
}
