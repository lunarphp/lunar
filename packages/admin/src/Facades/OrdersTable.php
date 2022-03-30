<?php

namespace GetCandy\Hub\Facades;

use GetCandy\Hub\Base\OrdersTableInterface;
use Illuminate\Support\Facades\Facade;

class OrdersTable extends Facade
{
    public static function getFacadeAccessor()
    {
        return OrdersTableInterface::class;
    }
}
