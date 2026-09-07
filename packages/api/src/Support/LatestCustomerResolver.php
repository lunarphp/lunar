<?php

namespace Lunar\Api\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Api\Contracts\CustomerResolver;
use Lunar\Core\Contracts\LunarUser;
use Lunar\Core\Models\Customer;

final class LatestCustomerResolver implements CustomerResolver
{
    public function resolve(Authenticatable $user): ?Customer
    {
        return $user instanceof LunarUser ? $user->latestCustomer() : null;
    }
}
