<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Cart;

/**
 * @method static void addOption(ShippingOption $option)
 * @method static void addOptions(Collection $options)
 * @method static void clearOptions()
 * @method static \Lunar\Core\Manifests\ShippingManifest getOptionUsing(\Closure $closure)
 * @method static Collection getOptions(Cart $cart)
 * @method static ShippingOption|null getOption(Cart $cart, string $identifier)
 * @method static ShippingOption|null getShippingOption(Cart $cart)
 *
 * @see \Lunar\Core\Manifests\ShippingManifest
 */
class ShippingManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\ShippingManifest::class;
    }
}
