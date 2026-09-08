<?php

namespace Lunar\Shipping;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Modifiers\ShippingModifiers;
use Lunar\Shipping\Actions\ShippingExclusionLists\CreateShippingExclusionList;
use Lunar\Shipping\Actions\ShippingExclusionLists\DeleteShippingExclusionList;
use Lunar\Shipping\Actions\ShippingExclusionLists\UpdateShippingExclusionList;
use Lunar\Shipping\Actions\ShippingMethods\CreateShippingMethod;
use Lunar\Shipping\Actions\ShippingMethods\DeleteShippingMethod;
use Lunar\Shipping\Actions\ShippingMethods\UpdateShippingMethod;
use Lunar\Shipping\Actions\ShippingRates\DeleteShippingRate;
use Lunar\Shipping\Actions\ShippingRates\SaveShippingRate;
use Lunar\Shipping\Actions\ShippingZones\CreateShippingZone;
use Lunar\Shipping\Actions\ShippingZones\DeleteShippingZone;
use Lunar\Shipping\Actions\ShippingZones\UpdateShippingZone;
use Lunar\Shipping\Contracts\Actions;
use Lunar\Shipping\DiscountTypes\ShippingDiscount;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;
use Lunar\Shipping\Managers\PostcodeManager;
use Lunar\Shipping\Managers\ShippingManager;
use Lunar\Shipping\Models\ShippingExclusion;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Models\ShippingZonePostcode;
use Lunar\Shipping\Observers\OrderObserver;
use Lunar\Shipping\Resolvers\PostcodeResolver;

class ShippingServiceProvider extends ServiceProvider
{
    /**
     * The package's swappable action seams. A consumer overrides one by
     * binding the same contract in their own service provider.
     *
     * @var array<class-string, class-string>
     */
    protected array $actions = [
        Actions\ShippingZones\CreatesShippingZone::class => CreateShippingZone::class,
        Actions\ShippingZones\UpdatesShippingZone::class => UpdateShippingZone::class,
        Actions\ShippingZones\DeletesShippingZone::class => DeleteShippingZone::class,
        Actions\ShippingMethods\CreatesShippingMethod::class => CreateShippingMethod::class,
        Actions\ShippingMethods\UpdatesShippingMethod::class => UpdateShippingMethod::class,
        Actions\ShippingMethods\DeletesShippingMethod::class => DeleteShippingMethod::class,
        Actions\ShippingRates\SavesShippingRate::class => SaveShippingRate::class,
        Actions\ShippingRates\DeletesShippingRate::class => DeleteShippingRate::class,
        Actions\ShippingExclusionLists\CreatesShippingExclusionList::class => CreateShippingExclusionList::class,
        Actions\ShippingExclusionLists\UpdatesShippingExclusionList::class => UpdateShippingExclusionList::class,
        Actions\ShippingExclusionLists\DeletesShippingExclusionList::class => DeleteShippingExclusionList::class,
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shipping-tables.php', 'lunar.shipping-tables');

        foreach ($this->actions as $contract => $concrete) {
            $this->app->bind($contract, $concrete);
        }

        $this->app->singleton(PostcodeManager::class, function () {
            $manager = new PostcodeManager;
            $manager->addResolver(PostcodeResolver::class);

            return $manager;
        });
    }

    public function boot(ShippingModifiers $shippingModifiers)
    {
        if (! config('lunar.shipping-tables.enabled')) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'shipping');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shipping');

        $shippingModifiers->add(
            ShippingModifier::class,
        );

        Discounts::addType(ShippingDiscount::class);

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

        ModelManifest::addDirectory(
            __DIR__.'/Models'
        );

        Relation::morphMap([
            'shipping_exclusion' => ShippingExclusion::class,
            'shipping_exclusion_list' => ShippingExclusionList::class,
            'shipping_method' => ShippingMethod::class,
            'shipping_rate' => ShippingRate::class,
            'shipping_zone' => ShippingZone::class,
            'shipping_zone_postcode' => ShippingZonePostcode::class,
        ]);
    }
}
