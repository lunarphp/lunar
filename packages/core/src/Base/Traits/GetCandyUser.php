<?php

namespace GetCandy\Base\Traits;

use GetCandy\Models\Customer;

trait GetCandyUser
{
    public function customers()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(Customer::class, "{$prefix}customer_user");
    }
}
