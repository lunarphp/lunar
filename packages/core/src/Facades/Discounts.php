<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\DiscountManager;
use Lunar\Core\DataObjects\CartDiscount;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\CustomerGroup;

/**
 * @method static \Lunar\Core\Managers\DiscountManager channel(Channel|\Traversable|array $channel)
 * @method static \Lunar\Core\Managers\DiscountManager customerGroup(CustomerGroup|\Traversable|array $customerGroups)
 * @method static Collection getChannels()
 * @method static Collection getDiscounts(Cart|null $cart = null)
 * @method static Collection getCustomerGroups()
 * @method static \Lunar\Core\Managers\DiscountManager addType(string $classname)
 * @method static Collection getTypes()
 * @method static \Lunar\Core\Managers\DiscountManager addApplied(CartDiscount $cartDiscount)
 * @method static Collection getApplied()
 * @method static \Lunar\Core\Models\Cart apply(\Lunar\Core\Models\Cart $cart)
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
