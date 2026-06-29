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
use Lunar\Core\Models\CustomerGroup;

/**
 * @method static \Lunar\Core\Managers\PricingManager for(Purchasable $purchasable)
 * @method static \Lunar\Core\Managers\PricingManager user(Authenticatable|null $user)
 * @method static \Lunar\Core\Managers\PricingManager guest()
 * @method static \Lunar\Core\Managers\PricingManager currency(Currency|null $currency)
 * @method static \Lunar\Core\Managers\PricingManager qty(int $qty)
 * @method static \Lunar\Core\Managers\PricingManager customerGroups(Collection|null $customerGroups)
 * @method static \Lunar\Core\Managers\PricingManager customerGroup(CustomerGroup|null $customerGroup)
 * @method static \Lunar\Core\Managers\PricingManager using(StorefrontContext $context)
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
