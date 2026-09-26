<?php

namespace Lunar\Api\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Core\Models\Customer;

/**
 * Which customer an authenticated storefront user acts as. The default takes
 * the user's latest customer; hosts bind their own to change that.
 */
interface CustomerResolver
{
    public function resolve(Authenticatable $user): ?Customer;
}
