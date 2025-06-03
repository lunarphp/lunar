<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\PricingManagerInterface;

/**
 * @method static \Lunar\Managers\PricingManager for(\Lunar\Base\Purchasable $purchasable)
 * @method static \Lunar\Managers\PricingManager user(\Illuminate\Contracts\Auth\Authenticatable|null $user)
 * @method static \Lunar\Managers\PricingManager guest()
 * @method static \Lunar\Managers\PricingManager currency(\Lunar\Models\Contracts\Currency|null $currency)
 * @method static \Lunar\Managers\PricingManager qty(int $qty)
 * @method static \Lunar\Managers\PricingManager customerGroups(\Illuminate\Support\Collection|null $customerGroups)
 * @method static \Lunar\Managers\PricingManager customerGroup(\Lunar\Models\Contracts\CustomerGroup|null $customerGroup)
 * @method static \Lunar\Base\DataTransferObjects\PricingResponse get()
 *
 * @see \Lunar\Managers\PricingManager
 */
class Pricing extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return PricingManagerInterface::class;
    }
}
