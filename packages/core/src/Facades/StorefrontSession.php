<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\StorefrontSessionInterface;

/**
 * @method static \Lunar\Models\Contracts\Channel getChannel()
 * @method static \Lunar\Managers\StorefrontSessionManager setChannel(\Lunar\Models\Contracts\Channel $channel)
 * @method static \Illuminate\Support\Collection getCustomerGroups()
 * @method static \Lunar\Managers\StorefrontSessionManager setCustomerGroups(\Illuminate\Support\Collection $customerGroups)
 * @method static \Lunar\Managers\StorefrontSessionManager setCustomerGroup(\Lunar\Models\Contracts\CustomerGroup $customerGroup)
 * @method static \Lunar\Managers\StorefrontSessionManager resetCustomerGroups()
 * @method static \Lunar\Models\Contracts\Currency getCurrency()
 * @method static \Lunar\Managers\StorefrontSessionManager setCurrency(\Lunar\Models\Contracts\Currency $currency)
 * @method static \Lunar\Models\Contracts\Customer|null getCustomer()
 * @method static \Lunar\Managers\StorefrontSessionManager setCustomer(\Lunar\Models\Contracts\Customer $customer)
 * @method static void initChannel()
 * @method static void initCustomerGroups()
 * @method static void initCurrency()
 * @method static void initCustomer()
 * @method static void forget()
 * @method static string getSessionKey()
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
