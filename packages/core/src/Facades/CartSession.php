<?php

namespace Lunar\Core\Facades;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Managers\CartSessionManager;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;

/**
 * @method static bool allowsMultipleOrdersPerCart()
 * @method static Cart|null current(bool $estimateShipping = false, bool $calculate = true)
 * @method static CartSessionManager estimateShippingUsing(array $meta)
 * @method static array getShippingEstimateMeta()
 * @method static void forget(bool $delete = true)
 * @method static Cart|null manager()
 * @method static void associate(\Lunar\Core\Models\Cart $cart, Authenticatable $user, string $policy)
 * @method static \Lunar\Core\Models\Cart use(\Lunar\Core\Models\Cart $cart)
 * @method static void estimateShipping()
 * @method static string getSessionKey()
 * @method static void setChannel(Channel $channel)
 * @method static void setCurrency(Currency $currency)
 * @method static Currency getCurrency()
 * @method static Channel getChannel()
 * @method static Collection getShippingOptions()
 * @method static Order createOrder(bool $forget = true)
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
