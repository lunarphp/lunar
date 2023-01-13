<?php

namespace Lunar;

use Cartalyst\Converter\Laravel\Facades\Converter;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Lunar\Addons\Manifest;
use Lunar\Base\AttributeManifest;
use Lunar\Base\AttributeManifestInterface;
use Lunar\Base\CartLineModifiers;
use Lunar\Base\CartModifiers;
use Lunar\Base\CartSessionInterface;
use Lunar\Base\DiscountManagerInterface;
use Lunar\Base\FieldTypeManifest;
use Lunar\Base\FieldTypeManifestInterface;
use Lunar\Base\ModelManifest;
use Lunar\Base\ModelManifestInterface;
use Lunar\Base\OrderModifiers;
use Lunar\Base\OrderReferenceGenerator;
use Lunar\Base\OrderReferenceGeneratorInterface;
use Lunar\Base\PaymentManagerInterface;
use Lunar\Base\PricingManagerInterface;
use Lunar\Base\ShippingManifest;
use Lunar\Base\ShippingManifestInterface;
use Lunar\Base\ShippingModifiers;
use Lunar\Base\TaxManagerInterface;
use Lunar\Console\Commands\AddonsDiscover;
use Lunar\Console\Commands\Import\AddressData;
use Lunar\Console\Commands\MigrateGetCandy;
use Lunar\Console\Commands\Orders\SyncNewCustomerOrders;
use Lunar\Console\Commands\ScoutIndexer;
use Lunar\Console\InstallLunar;
use Lunar\Database\State\ConvertProductTypeAttributesToProducts;
use Lunar\Database\State\EnsureBrandsAreUpgraded;
use Lunar\Database\State\EnsureDefaultTaxClassExists;
use Lunar\Database\State\EnsureMediaCollectionsAreRenamed;
use Lunar\Listeners\CartSessionAuthListener;
use Lunar\Managers\CartSessionManager;
use Lunar\Managers\DiscountManager;
use Lunar\Managers\PaymentManager;
use Lunar\Managers\PricingManager;
use Lunar\Managers\TaxManager;
use Lunar\Models\Address;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\Transaction;
use Lunar\Models\Url;
use Lunar\Observers\AddressObserver;
use Lunar\Observers\CartLineObserver;
use Lunar\Observers\ChannelObserver;
use Lunar\Observers\CollectionObserver;
use Lunar\Observers\CurrencyObserver;
use Lunar\Observers\CustomerGroupObserver;
use Lunar\Observers\LanguageObserver;
use Lunar\Observers\OrderLineObserver;
use Lunar\Observers\OrderObserver;
use Lunar\Observers\TransactionObserver;
use Lunar\Observers\UrlObserver;

class LunarServiceProvider extends ServiceProvider
{
    protected $configFiles = [
        'database',
        'media',
        'shipping',
        'taxes',
        'cart',
        'orders',
        'urls',
        'search',
        'payments',
    ];

    protected $root = __DIR__.'/..';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        collect($this->configFiles)->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "lunar.$config");
        });

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunar');

        $this->registerAddonManifest();

        $this->app->singleton(CartModifiers::class, function () {
            return new CartModifiers();
        });

        $this->app->singleton(CartLineModifiers::class, function () {
            return new CartLineModifiers();
        });

        $this->app->singleton(OrderModifiers::class, function () {
            return new OrderModifiers();
        });

        $this->app->singleton(CartSessionInterface::class, function ($app) {
            return $app->make(CartSessionManager::class);
        });

        $this->app->singleton(ShippingModifiers::class, function ($app) {
            return new ShippingModifiers();
        });

        $this->app->singleton(ShippingManifestInterface::class, function ($app) {
            return $app->make(ShippingManifest::class);
        });

        $this->app->singleton(OrderReferenceGeneratorInterface::class, function ($app) {
            return $app->make(OrderReferenceGenerator::class);
        });

        $this->app->singleton(AttributeManifestInterface::class, function ($app) {
            return $app->make(AttributeManifest::class);
        });

        $this->app->singleton(FieldTypeManifestInterface::class, function ($app) {
            return $app->make(FieldTypeManifest::class);
        });

        $this->app->singleton(ModelManifestInterface::class, function ($app) {
            return $app->make(ModelManifest::class);
        });

        $this->app->bind(PricingManagerInterface::class, function ($app) {
            return $app->make(PricingManager::class);
        });

        $this->app->singleton(TaxManagerInterface::class, function ($app) {
            return $app->make(TaxManager::class);
        });

        $this->app->singleton(PaymentManagerInterface::class, function ($app) {
            return $app->make(PaymentManager::class);
        });

        $this->app->singleton(DiscountManagerInterface::class, function ($app) {
            return $app->make(DiscountManager::class);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Relation::morphMap([
            'product_type' => Lunar\Models\ProductType::class,
            //'order' => Lunar\Models\Order::class,
        ]);

        $this->registerObservers();
        $this->registerBlueprintMacros();
        $this->registerStateListeners();

        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("lunar/$config.php"),
                ], 'lunar');
            });

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'lunar.migrations');

            $this->commands([
                InstallLunar::class,
                AddonsDiscover::class,
                AddressData::class,
                ScoutIndexer::class,
                MigrateGetCandy::class,
                SyncNewCustomerOrders::class,
            ]);
        }

        Arr::macro('permutate', [\Lunar\Utils\Arr::class, 'permutate']);

        // Handle generator
        Str::macro('handle', function ($string) {
            return Str::slug($string, '_');
        });

        Converter::setMeasurements(
            config('lunar.shipping.measurements', [])
        );

        Event::listen(
            Login::class,
            [CartSessionAuthListener::class, 'login']
        );

        Event::listen(
            Logout::class,
            [CartSessionAuthListener::class, 'logout']
        );
    }

    protected function registerAddonManifest()
    {
        $this->app->instance(Manifest::class, new Manifest(
            new Filesystem(),
            $this->app->basePath(),
            $this->app->bootstrapPath().'/cache/lunar_addons.php'
        ));
    }

    protected function registerStateListeners()
    {
        $states = [
            ConvertProductTypeAttributesToProducts::class,
            EnsureDefaultTaxClassExists::class,
            EnsureBrandsAreUpgraded::class,
            EnsureMediaCollectionsAreRenamed::class,
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

    /**
     * Register the observers used in Lunar.
     *
     * @return void
     */
    protected function registerObservers(): void
    {
        Channel::observe(ChannelObserver::class);
        CustomerGroup::observe(CustomerGroupObserver::class);
        Language::observe(LanguageObserver::class);
        Currency::observe(CurrencyObserver::class);
        Url::observe(UrlObserver::class);
        Collection::observe(CollectionObserver::class);
        CartLine::observe(CartLineObserver::class);
        Order::observe(OrderObserver::class);
        OrderLine::observe(OrderLineObserver::class);
        Address::observe(AddressObserver::class);
        Transaction::observe(TransactionObserver::class);
    }

    /**
     * Register the blueprint macros.
     *
     * @return void
     */
    protected function registerBlueprintMacros(): void
    {
        Blueprint::macro('scheduling', function () {
            $this->boolean('enabled')->default(false)->index();
            $this->timestamp('starts_at')->nullable()->index();
            $this->timestamp('ends_at')->nullable()->index();
        });

        Blueprint::macro('dimensions', function () {
            $columns = ['length', 'width', 'height', 'weight', 'volume'];
            foreach ($columns as $column) {
                $this->decimal("{$column}_value", 10, 4)->default(0)->nullable()->index();
                $this->string("{$column}_unit")->default('mm')->nullable();
            }
        });

        Blueprint::macro('userForeignKey', function ($field_name = 'user_id', $nullable = false) {
            $userModel = config('auth.providers.users.model');

            $type = config('lunar.database.users_id_type', 'bigint');

            if ($type == 'uuid') {
                $this->foreignUuId($field_name)
                     ->nullable($nullable)
                     ->constrained(
                         (new $userModel())->getTable()
                     );
            } elseif ($type == 'int') {
                $this->unsignedInteger($field_name)->nullable($nullable);
                $this->foreign($field_name)->references('id')->on('users');
            } else {
                $this->foreignId($field_name)
                     ->nullable($nullable)
                     ->constrained(
                         (new $userModel())->getTable()
                     );
            }
        });
    }
}
