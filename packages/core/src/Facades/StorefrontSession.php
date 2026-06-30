<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Managers\StorefrontSessionManager;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Region;

/**
 * @method static StorefrontContext context()
 * @method static Region|null getRegion()
 * @method static StorefrontSessionManager setRegion(Region $region)
 * @method static void forget()
 * @method static void initCustomerGroups()
 * @method static void initChannel()
 * @method static Customer|null initCustomer()
 * @method static string getSessionKey()
 * @method static StorefrontSessionManager setChannel(Channel|string $channel)
 * @method static StorefrontSessionManager setCustomer(Customer $customer)
 * @method static Customer|null getCustomer()
 * @method static StorefrontSessionManager setCustomerGroups(Collection $customerGroups)
 * @method static StorefrontSessionManager setCustomerGroup(CustomerGroup $customerGroup)
 * @method static StorefrontSessionManager resetCustomerGroups()
 * @method static Channel getChannel()
 * @method static Collection|null getCustomerGroups()
 * @method static StorefrontSessionManager setCurrency(Currency $currency)
 * @method static Currency getCurrency()
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
