<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\DiscountManager;

/**
 * @method static \Lunar\Core\Managers\DiscountManager channel(\Lunar\Core\Models\Contracts\Channel|\Traversable|array $channel)
 * @method static \Lunar\Core\Managers\DiscountManager customerGroup(\Lunar\Core\Models\Contracts\CustomerGroup|\Traversable|array $customerGroups)
 * @method static \Illuminate\Support\Collection getChannels()
 * @method static \Illuminate\Support\Collection getDiscounts(\Lunar\Core\Models\Cart|null $cart = null)
 * @method static \Illuminate\Support\Collection getCustomerGroups()
 * @method static \Lunar\Core\Managers\DiscountManager addType(string $classname)
 * @method static \Illuminate\Support\Collection getTypes()
 * @method static \Lunar\Core\Managers\DiscountManager addApplied(\Lunar\Core\DataObjects\CartDiscount $cartDiscount)
 * @method static \Illuminate\Support\Collection getApplied()
 * @method static \Lunar\Core\Models\Contracts\Cart apply(\Lunar\Core\Models\Contracts\Cart $cart)
 * @method static \Lunar\Core\Managers\DiscountManager resetDiscounts()
 * @method static bool validateCoupon(string $coupon)
 *
 * @see DiscountManager
 */
class Discounts extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return DiscountManager::class;
    }
}
