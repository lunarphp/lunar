<?php

namespace Lunar\Shipping;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\Translator;
use Lunar\Base\ShippingModifiers;
use Lunar\Facades\Discounts;
use Lunar\Facades\ModelManifest;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Shipping\Database\State\MigrateCutoffToSchedule;
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
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shipping-tables.php', 'lunar.shipping-tables');

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

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunarpanel.shipping');

        $this->mergeTranslationsForPanel();

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

        $this->registerStateListeners();

        Relation::morphMap([
            'shipping_exclusion' => ShippingExclusion::modelClass(),
            'shipping_exclusion_list' => ShippingExclusionList::modelClass(),
            'shipping_method' => ShippingMethod::modelClass(),
            'shipping_rate' => ShippingRate::modelClass(),
            'shipping_zone' => ShippingZone::modelClass(),
            'shipping_zone_postcode' => ShippingZonePostcode::modelClass(),
        ]);
    }

    protected function registerStateListeners(): void
    {
        $states = [
            MigrateCutoffToSchedule::class,
        ];

        foreach ($states as $state) {
            $class = new $state;

            Event::listen(
                [MigrationsStarted::class],
                [$class, 'prepare']
            );

            Event::listen(
                [MigrationsEnded::class, NoPendingMigrations::class],
                [$class, 'run']
            );
        }
    }

    private function mergeTranslationsForPanel(): void
    {
        $this->app->booted(function ($app) {
            /** @var Translator $translator */
            $translator = $app['translator'];

            $locale = $app->getLocale();
            $group = 'auth';
            $namespace = 'lunarpanel';

            $originalLines = $translator->get("{$namespace}::{$group}", [], $locale);

            if (! is_array($originalLines)) {
                $originalLines = [];
            }

            $langFilePath = __DIR__."/../resources/lang/{$locale}/{$group}.php";

            if (file_exists($langFilePath)) {
                $langLines = require $langFilePath;

                $mergedLines = collect(array_replace_recursive($originalLines, $langLines))->mapWithKeys(function ($line, $key) use ($group) {
                    return [
                        "{$group}.{$key}" => $line,
                    ];
                })->toArray();

                $translator->addLines($mergedLines, $locale, $namespace);
            }
        });
    }
}
