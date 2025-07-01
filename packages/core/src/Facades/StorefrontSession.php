<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\StorefrontSessionInterface;

/**
 * @method static void forget()
 * @method static void initCustomerGroups()
 * @method static void initChannel()
 * @method static \Lunar\Models\Contracts\Customer|null initCustomer()
 * @method static string getSessionKey()
 * @method static \Lunar\Managers\StorefrontSessionManager setChannel(\Lunar\Models\Contracts\Channel|string $channel)
 * @method static \Lunar\Managers\StorefrontSessionManager setCustomer(\Lunar\Models\Contracts\Customer $customer)
 * @method static \Lunar\Models\Contracts\Customer|null getCustomer()
 * @method static \Lunar\Managers\StorefrontSessionManager setCustomerGroups(\Illuminate\Support\Collection $customerGroups)
 * @method static \Lunar\Managers\StorefrontSessionManager setCustomerGroup(\Lunar\Models\Contracts\CustomerGroup $customerGroup)
 * @method static \Lunar\Managers\StorefrontSessionManager resetCustomerGroups()
 * @method static \Lunar\Models\Contracts\Channel getChannel()
 * @method static \Illuminate\Support\Collection|null getCustomerGroups()
 * @method static \Lunar\Managers\StorefrontSessionManager setCurrency(\Lunar\Models\Contracts\Currency $currency)
 * @method static \Lunar\Models\Contracts\Currency getCurrency()
 *
 * @see \Lunar\Managers\StorefrontSessionManager
 */
class StorefrontSession extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StorefrontSessionInterface::class;
    }
}
