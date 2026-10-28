<?php

namespace Lunar\Core\Facades;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\PricingManager;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\DataObjects\PricingResponse;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;

/**
 * @method static PricingManager for(Purchasable $purchasable)
 * @method static PricingManager user(Authenticatable|null $user)
 * @method static PricingManager guest()
 * @method static PricingManager currency(Currency|null $currency)
 * @method static PricingManager qty(int $qty)
 * @method static PricingManager customerGroups(Collection|null $customerGroups)
 * @method static PricingManager customerGroup(CustomerGroup|null $customerGroup)
 * @method static PricingManager using(StorefrontContext $context)
 * @method static PricingManager customer(Customer|null $customer)
 * @method static StorefrontContext getContext()
 * @method static void preventDefaultContext(bool $value = true)
 * @method static PricingResponse get()
 *
 * @see PricingManager
 */
class Pricing extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return PricingManager::class;
    }
}
