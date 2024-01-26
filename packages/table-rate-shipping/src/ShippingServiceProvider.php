<?php

namespace Lunar\Shipping;

use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Base\ShippingModifiers;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Observers\OrderObserver;
use Lunar\Shipping\Filament\Extensions\OrderResourceExtension;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;
use Lunar\Shipping\Managers\ShippingManager;
use Lunar\Shipping\Models\ShippingExclusion;
use Lunar\Shipping\Models\ShippingZone;

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

        LunarPanel::registerExtension(new OrderResourceExtension(), OrderResource::class);
    }
}
