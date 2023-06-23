<?php

namespace Lunar\Base\Traits;

use Lunar\Models\Customer;
use Lunar\Models\Order;

trait LunarUser
{
    public function customers()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(Customer::class, "{$prefix}customer_user");
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
