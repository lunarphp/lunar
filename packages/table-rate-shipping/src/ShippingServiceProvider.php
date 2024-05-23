<?php

namespace Lunar\Shipping;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Lunar\Base\ShippingModifiers;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;
use Lunar\Shipping\Managers\ShippingManager;
use Lunar\Shipping\Models\ShippingExclusion;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Models\ShippingZonePostcode;
use Lunar\Shipping\Observers\OrderObserver;

class ShippingServiceProvider extends ServiceProvider
{
    public function boot(ShippingModifiers $shippingModifiers)
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shipping-tables.php', 'lunar.shipping-tables');

        if (! config('lunar.shipping-tables.enabled')) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunarpanel.shipping');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shipping');

        $shippingModifiers->add(
            ShippingModifier::class,
        );

        Order::observe(OrderObserver::class);

        Order::resolveRelationUsing('shippingZone', function ($orderModel) {
            $prefix = config('lunar.database.table_prefix');

            return $orderModel->belongsToMany(
                ShippingZone::class,
                "{$prefix}order_shipping_zone"
            )->withTimestamps();
        });

        Product::resolveRelationUsing('shippingExclusions', function ($product) {
            return $product->morphMany(ShippingExclusion::class, 'purchasable');
        });

        $this->app->bind(ShippingMethodManagerInterface::class, function ($app) {
            return $app->make(ShippingManager::class);
        });

        ModelManifest::addDirectory(
            __DIR__.'/Models'
        );

        Relation::morphMap([
            'shipping_exclusion' => ShippingExclusion::modelClass(),
            'shipping_exclusion_list' => ShippingExclusionList::modelClass(),
            'shipping_method' => ShippingMethod::modelClass(),
            'shipping_rate' => ShippingRate::modelClass(),
            'shipping_zone' => ShippingZone::modelClass(),
            'shipping_zone_postcode' => ShippingZonePostcode::modelClass(),
        ]);
    }
}
