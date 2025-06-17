<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\DiscountManagerInterface;

/**
 * @method static \Lunar\Managers\DiscountManager channel(\Lunar\Models\Contracts\Channel|\Traversable|array $channel)
 * @method static \Lunar\Managers\DiscountManager customerGroup(\Lunar\Models\Contracts\CustomerGroup|\Traversable|array $customerGroups)
 * @method static \Illuminate\Support\Collection getChannels()
 * @method static \Illuminate\Support\Collection getDiscounts(\Lunar\Models\Cart|null $cart = null)
 * @method static \Illuminate\Support\Collection getCustomerGroups()
 * @method static \Lunar\Managers\DiscountManager addType(string $classname)
 * @method static \Illuminate\Support\Collection getTypes()
 * @method static \Lunar\Managers\DiscountManager addApplied(\Lunar\Base\DataTransferObjects\CartDiscount $cartDiscount)
 * @method static \Illuminate\Support\Collection getApplied()
 * @method static \Lunar\Models\Contracts\Cart apply(\Lunar\Models\Contracts\Cart $cart)
 * @method static \Lunar\Managers\DiscountManager resetDiscounts()
 * @method static bool validateCoupon(string $coupon)
 *
 * @see \Lunar\Managers\DiscountManager
 */
class Discounts extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return DiscountManagerInterface::class;
    }
}
