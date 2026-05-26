<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Managers\CartSessionManager;

/**
 * @method static bool allowsMultipleOrdersPerCart()
 * @method static \Lunar\Core\Models\Cart|null current(bool $estimateShipping = false, bool $calculate = true)
 * @method static \Lunar\Core\Managers\CartSessionManager estimateShippingUsing(array $meta)
 * @method static array getShippingEstimateMeta()
 * @method static void forget(bool $delete = true)
 * @method static \Lunar\Core\Models\Cart|null manager()
 * @method static void associate(\Lunar\Core\Models\Contracts\Cart $cart, \Illuminate\Contracts\Auth\Authenticatable $user, string $policy)
 * @method static \Lunar\Core\Models\Contracts\Cart use(\Lunar\Core\Models\Contracts\Cart $cart)
 * @method static void estimateShipping()
 * @method static string getSessionKey()
 * @method static void setChannel(\Lunar\Core\Models\Contracts\Channel $channel)
 * @method static void setCurrency(\Lunar\Core\Models\Contracts\Currency $currency)
 * @method static \Lunar\Core\Models\Contracts\Currency getCurrency()
 * @method static \Lunar\Core\Models\Contracts\Channel getChannel()
 * @method static \Illuminate\Support\Collection getShippingOptions()
 * @method static \Lunar\Core\Models\Order createOrder(bool $forget = true)
 *
 * @see CartSessionManager
 */
class CartSession extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\CartSession::class;
    }
}
