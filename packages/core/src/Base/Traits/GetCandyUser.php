<?php

namespace Lunar\Base\Traits;

use Lunar\Models\Customer;

trait GetCandyUser
{
    public function customers()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(Customer::class, "{$prefix}customer_user");
    }
}
