<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\CartSessionInterface;

/**
 * @method static bool allowsMultipleOrdersPerCart()
 * @method static \Lunar\Models\Cart|null current(bool $estimateShipping = false, bool $calculate = true)
 * @method static \Lunar\Managers\CartSessionManager estimateShippingUsing(array $meta)
 * @method static array getShippingEstimateMeta()
 * @method static void forget(bool $delete = true)
 * @method static \Lunar\Models\Cart|null manager()
 * @method static void associate(\Lunar\Models\Contracts\Cart $cart, \Illuminate\Contracts\Auth\Authenticatable $user, string $policy)
 * @method static \Lunar\Models\Contracts\Cart use(\Lunar\Models\Contracts\Cart $cart)
 * @method static void estimateShipping()
 * @method static string getSessionKey()
 * @method static void setChannel(\Lunar\Models\Contracts\Channel $channel)
 * @method static void setCurrency(\Lunar\Models\Contracts\Currency $currency)
 * @method static \Lunar\Models\Contracts\Currency getCurrency()
 * @method static \Lunar\Models\Contracts\Channel getChannel()
 * @method static \Illuminate\Support\Collection getShippingOptions()
 * @method static \Lunar\Models\Order createOrder(bool $forget = true)
 *
 * @see \Lunar\Managers\CartSessionManager
 */
class CartSession extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return CartSessionInterface::class;
    }
}
