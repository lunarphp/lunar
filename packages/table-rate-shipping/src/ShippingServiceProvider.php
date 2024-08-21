<?php

namespace Lunar\Shipping;

use Illuminate\Support\ServiceProvider;
use Lunar\Base\ShippingModifiers;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;
use Lunar\Shipping\Managers\ShippingManager;
use Lunar\Shipping\Models\ShippingExclusion;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Observers\OrderObserver;

class ShippingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shipping-tables.php', 'lunar.shipping-tables');
    }

    public function boot(ShippingModifiers $shippingModifiers)
    {
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

        CustomerGroup::resolveRelationUsing('shippingMethods', function ($customerGroup) {
            $prefix = config('lunar.database.table_prefix');

            return $customerGroup->belongsToMany(
                ShippingMethod::class,
                "{$prefix}customer_group_shipping_method"
            )->withTimestamps();
        });

        Product::resolveRelationUsing('shippingExclusions', function ($product) {
            return $product->morphMany(ShippingExclusion::class, 'purchasable');
        });

        $this->app->bind(ShippingMethodManagerInterface::class, function ($app) {
            return $app->make(ShippingManager::class);
        });
    }
}
