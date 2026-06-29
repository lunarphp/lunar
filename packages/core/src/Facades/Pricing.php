<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\PricingManager;

/**
 * @method static \Lunar\Core\Managers\PricingManager for(\Lunar\Core\Contracts\Purchasable $purchasable)
 * @method static \Lunar\Core\Managers\PricingManager user(\Illuminate\Contracts\Auth\Authenticatable|null $user)
 * @method static \Lunar\Core\Managers\PricingManager guest()
 * @method static \Lunar\Core\Managers\PricingManager currency(\Lunar\Core\Models\Contracts\Currency|null $currency)
 * @method static \Lunar\Core\Managers\PricingManager qty(int $qty)
 * @method static \Lunar\Core\Managers\PricingManager customerGroups(\Illuminate\Support\Collection|null $customerGroups)
 * @method static \Lunar\Core\Managers\PricingManager customerGroup(\Lunar\Core\Models\Contracts\CustomerGroup|null $customerGroup)
 * @method static \Lunar\Core\Managers\PricingManager using(\Lunar\Core\DataObjects\StorefrontContext $context)
 * @method static \Lunar\Core\DataObjects\PricingResponse get()
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
