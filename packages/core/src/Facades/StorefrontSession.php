<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Managers\StorefrontSessionManager;

/**
 * @method static \Lunar\Core\DataObjects\StorefrontContext context()
 * @method static \Lunar\Core\Models\Contracts\Region|null getRegion()
 * @method static \Lunar\Core\Managers\StorefrontSessionManager setRegion(\Lunar\Core\Models\Contracts\Region $region)
 * @method static void forget()
 * @method static void initCustomerGroups()
 * @method static void initChannel()
 * @method static \Lunar\Core\Models\Contracts\Customer|null initCustomer()
 * @method static string getSessionKey()
 * @method static \Lunar\Core\Managers\StorefrontSessionManager setChannel(\Lunar\Core\Models\Contracts\Channel|string $channel)
 * @method static \Lunar\Core\Managers\StorefrontSessionManager setCustomer(\Lunar\Core\Models\Contracts\Customer $customer)
 * @method static \Lunar\Core\Models\Contracts\Customer|null getCustomer()
 * @method static \Lunar\Core\Managers\StorefrontSessionManager setCustomerGroups(\Illuminate\Support\Collection $customerGroups)
 * @method static \Lunar\Core\Managers\StorefrontSessionManager setCustomerGroup(\Lunar\Core\Models\Contracts\CustomerGroup $customerGroup)
 * @method static \Lunar\Core\Managers\StorefrontSessionManager resetCustomerGroups()
 * @method static \Lunar\Core\Models\Contracts\Channel getChannel()
 * @method static \Illuminate\Support\Collection|null getCustomerGroups()
 * @method static \Lunar\Core\Managers\StorefrontSessionManager setCurrency(\Lunar\Core\Models\Contracts\Currency $currency)
 * @method static \Lunar\Core\Models\Contracts\Currency getCurrency()
 *
 * @see StorefrontSessionManager
 */
class StorefrontSession extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Lunar\Core\Contracts\StorefrontSession::class;
    }
}
