<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Base\ShippingManifestInterface;

/**
 * @method static void addOption(\Lunar\Core\DataTypes\ShippingOption $option)
 * @method static void addOptions(\Illuminate\Support\Collection $options)
 * @method static void clearOptions()
 * @method static \Lunar\Core\Base\ShippingManifest getOptionUsing(\Closure $closure)
 * @method static \Illuminate\Support\Collection getOptions(\Lunar\Core\Models\Contracts\Cart $cart)
 * @method static \Lunar\Core\DataTypes\ShippingOption|null getOption(\Lunar\Core\Models\Contracts\Cart $cart, string $identifier)
 * @method static \Lunar\Core\DataTypes\ShippingOption|null getShippingOption(\Lunar\Core\Models\Contracts\Cart $cart)
 *
 * @see \Lunar\Core\Base\ShippingManifest
 */
class ShippingManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ShippingManifestInterface::class;
    }
}
