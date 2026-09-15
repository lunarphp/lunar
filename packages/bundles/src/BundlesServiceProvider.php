<?php

namespace Lunar\Bundles;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Lunar\Bundles\Actions\DefineBundle;
use Lunar\Bundles\Actions\DeleteBundle;
use Lunar\Bundles\Actions\RepriceBundle;
use Lunar\Bundles\Actions\ResolveBundleInventory;
use Lunar\Bundles\Actions\ResolveBundleSelection;
use Lunar\Bundles\Actions\SyncBundleComponents;
use Lunar\Bundles\Actions\SyncBundleGroups;
use Lunar\Bundles\Console\RepriceBundlesCommand;
use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Contracts\Actions\DeletesBundle;
use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Contracts\Actions\ResolvesBundleSelection;
use Lunar\Bundles\Contracts\Actions\SyncsBundleComponents;
use Lunar\Bundles\Contracts\Actions\SyncsBundleGroups;
use Lunar\Bundles\Listeners\InvalidateContainingBundles;
use Lunar\Bundles\Listeners\RepriceOnComponentInvalidation;
use Lunar\Bundles\Listeners\RepriceOnComponentPriceChange;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Bundles\Pipelines\CartLine\PriceBundleSelection;
use Lunar\Bundles\Pipelines\Order\Creation\CreateBundleComponentLines;
use Lunar\Bundles\Validation\CartLine\BundleSelection;
use Lunar\Core\Contracts\Actions\Products\ResolvesInventory;
use Lunar\Core\Events\Catalog\ProductInvalidated;
use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

class BundlesServiceProvider extends ServiceProvider
{
    protected string $root = __DIR__.'/..';

    /**
     * Action contract => default implementation. Consumers swap one by
     * binding the same contract in their own service provider.
     *
     * @var array<class-string, class-string>
     */
    protected array $actions = [
        DefinesBundle::class => DefineBundle::class,
        DeletesBundle::class => DeleteBundle::class,
        RepricesBundle::class => RepriceBundle::class,
        ResolvesBundleSelection::class => ResolveBundleSelection::class,
        SyncsBundleComponents::class => SyncBundleComponents::class,
        SyncsBundleGroups::class => SyncBundleGroups::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom("{$this->root}/config/bundles.php", 'lunar.bundles');

        foreach ($this->actions as $contract => $concrete) {
            $this->app->bind($contract, $concrete);
        }

        // A bundle variant's inventory is derived from its components; every
        // other variant falls through to the core resolver.
        $this->app->extend(
            ResolvesInventory::class,
            fn (ResolvesInventory $inner) => new ResolveBundleInventory($inner),
        );
    }

    public function boot(): void
    {
        $this->publishes([
            "{$this->root}/config/bundles.php" => config_path('lunar/bundles.php'),
        ], 'lunar.bundles.config');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom("{$this->root}/database/migrations");
        }

        $this->loadTranslationsFrom("{$this->root}/resources/lang", 'bundles');

        ModelManifest::addDirectory(__DIR__.'/Models');

        Relation::morphMap([
            'bundle' => Bundle::class,
            'bundle_group' => BundleGroup::class,
            'bundle_component' => BundleComponent::class,
        ]);

        ProductVariant::resolveRelationUsing(
            'bundle',
            fn (ProductVariant $variant) => $variant->hasOne(Bundle::class, 'product_variant_id'),
        );

        Product::resolveRelationUsing(
            'bundles',
            fn (Product $product) => $product->hasManyThrough(Bundle::class, ProductVariant::class, 'product_id', 'product_variant_id'),
        );

        $this->registerCartHooks();
        $this->registerListeners();
        $this->registerConsole();
    }

    /**
     * Append the package's validator and stages to the core cart and order
     * config. Hosts that set those lists explicitly keep control of the order.
     */
    protected function registerCartHooks(): void
    {
        $append = function (string $key, string $class): void {
            $entries = config($key, []);

            if (! in_array($class, $entries, true)) {
                $entries[] = $class;
            }

            config()->set($key, $entries);
        };

        $append('lunar.cart.validators.add_to_cart', BundleSelection::class);
        $append('lunar.cart.validators.update_cart_line', BundleSelection::class);
        $append('lunar.cart.pipelines.cart_lines', PriceBundleSelection::class);
        $append('lunar.orders.pipelines.creation', CreateBundleComponentLines::class);
    }

    protected function registerListeners(): void
    {
        Price::observe(RepriceOnComponentPriceChange::class);
        Event::listen(ProductInvalidated::class, RepriceOnComponentInvalidation::class);
        Event::listen(ProductInvalidated::class, InvalidateContainingBundles::class);
    }

    protected function registerConsole(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            RepriceBundlesCommand::class,
        ]);
    }
}
