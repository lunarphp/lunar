<?php

namespace GetCandy;

use Cartalyst\Converter\Laravel\Facades\Converter;
use GetCandy\Addons\Manifest;
use GetCandy\Base\AttributeManifest;
use GetCandy\Base\AttributeManifestInterface;
use GetCandy\Base\CartLineModifiers;
use GetCandy\Base\CartModifiers;
use GetCandy\Base\CartSessionInterface;
use GetCandy\Base\FieldTypeManifest;
use GetCandy\Base\FieldTypeManifestInterface;
use GetCandy\Base\OrderModifiers;
use GetCandy\Base\OrderReferenceGenerator;
use GetCandy\Base\OrderReferenceGeneratorInterface;
use GetCandy\Base\ShippingManifest;
use GetCandy\Base\ShippingManifestInterface;
use GetCandy\Base\ShippingModifiers;
use GetCandy\Console\Commands\AddonsDiscover;
use GetCandy\Console\Commands\Import\AddressData;
use GetCandy\Console\Commands\MeilisearchSetup;
use GetCandy\Console\InstallGetCandy;
use GetCandy\Database\State\ConvertProductTypeAttributesToProducts;
use GetCandy\Database\State\EnsureDefaultTaxClassExists;
use GetCandy\Listeners\CartSessionAuthListener;
use GetCandy\Managers\CartSessionManager;
use GetCandy\Models\CartLine;
use GetCandy\Models\Channel;
use GetCandy\Models\Collection;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\OrderLine;
use GetCandy\Models\Url;
use GetCandy\Observers\CartLineObserver;
use GetCandy\Observers\ChannelObserver;
use GetCandy\Observers\CollectionObserver;
use GetCandy\Observers\CurrencyObserver;
use GetCandy\Observers\LanguageObserver;
use GetCandy\Observers\OrderLineObserver;
use GetCandy\Observers\UrlObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GetCandyServiceProvider extends ServiceProvider
{
    protected $configFiles = [
        'database',
        'media',
        'shipping',
        'taxes',
        'cart',
        'orders',
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
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "getcandy.$config");
        });

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'getcandy');
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
            'product_type' => GetCandy\Models\ProductType::class,
            //'order' => GetCandy\Models\Order::class,
        ]);

        $this->registerObservers();
        $this->registerAddonManifest();
        $this->registerBlueprintMacros();

        if (!$this->app->environment('testing')) {
            $this->registerStateListeners();
        }

        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("getcandy/$config.php"),
                ], 'getcandy');
            });

            $this->commands([
                InstallGetCandy::class,
                AddonsDiscover::class,
                MeilisearchSetup::class,
                AddressData::class,
            ]);
        }

        Arr::macro('permutate', [\GetCandy\Utils\Arr::class, 'permutate']);

        Converter::setMeasurements(
            config('getcandy.shipping.measurements', [])
        );

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
            $this->app->bootstrapPath().'/cache/getcandy_addons.php'
        ));
    }

    protected function registerStateListeners()
    {
        $states = [
            ConvertProductTypeAttributesToProducts::class,
            EnsureDefaultTaxClassExists::class,
        ];

        foreach ($states as $state) {
            Event::listen(
                MigrationsEnded::class,
                [$state, 'run']
            );
        }
    }

    /**
     * Register the observers used in GetCandy.
     *
     * @return void
     */
    protected function registerObservers(): void
    {
        Channel::observe(ChannelObserver::class);
        Language::observe(LanguageObserver::class);
        Currency::observe(CurrencyObserver::class);
        Url::observe(UrlObserver::class);
        Collection::observe(CollectionObserver::class);
        CartLine::observe(CartLineObserver::class);
        OrderLine::observe(OrderLineObserver::class);
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

            $type = config('getcandy.database.users_id_type', 'bigint');

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
