<?php

namespace Lunar\Core;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Builder;
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
use Lunar\Core\Addons\Manifest;
use Lunar\Core\Auth\Manifest as AccessControlManifest;
use Lunar\Core\Cache\AttributeCache as AttributeCacheImpl;
use Lunar\Core\Console\Commands\AddonsDiscover;
use Lunar\Core\Console\Commands\Import\AddressData;
use Lunar\Core\Console\Commands\Orders\SyncNewCustomerOrders;
use Lunar\Core\Console\Commands\PruneCarts;
use Lunar\Core\Console\Commands\ScoutIndexerCommand;
use Lunar\Core\Console\InstallLunar;
use Lunar\Core\Contracts\AttributeCache;
use Lunar\Core\Contracts\AttributeManifest;
use Lunar\Core\Contracts\CarrierManifest;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Contracts\CouponValidator;
use Lunar\Core\Contracts\DiscountManager;
use Lunar\Core\Contracts\FieldTypeManifest;
use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Contracts\FulfilmentStateConfig;
use Lunar\Core\Contracts\ModelManifest;
use Lunar\Core\Contracts\OrderReferenceGenerator;
use Lunar\Core\Contracts\PaymentManager;
use Lunar\Core\Contracts\PricingManager;
use Lunar\Core\Contracts\ProvidesTelemetryInsights;
use Lunar\Core\Contracts\ShippingManifest;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\Contracts\TaxManager;
use Lunar\Core\Contracts\TelemetryService;
use Lunar\Core\Database\State\EnsureBaseRolesAndPermissions;
use Lunar\Core\Events\Orders\OrderCancelled;
use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;
use Lunar\Core\Facades\Converter;
use Lunar\Core\Facades\Telemetry;
use Lunar\Core\Listeners\CartSessionAuthListener;
use Lunar\Core\Listeners\SendOrderCancelledNotifications;
use Lunar\Core\Listeners\SendOrderFulfilmentStatusNotifications;
use Lunar\Core\Listeners\SendOrderPaymentStatusNotifications;
use Lunar\Core\Managers\CartSessionManager;
use Lunar\Core\Managers\DiscountManager as DiscountManagerImpl;
use Lunar\Core\Managers\PaymentManager as PaymentManagerImpl;
use Lunar\Core\Managers\PricingManager as PricingManagerImpl;
use Lunar\Core\Managers\StorefrontSessionManager;
use Lunar\Core\Managers\TaxManager as TaxManagerImpl;
use Lunar\Core\Manifests\AttributeManifest as AttributeManifestImpl;
use Lunar\Core\Manifests\CarrierManifest as CarrierManifestImpl;
use Lunar\Core\Manifests\FieldTypeManifest as FieldTypeManifestImpl;
use Lunar\Core\Manifests\FulfilmentMethodManifest as FulfilmentMethodManifestImpl;
use Lunar\Core\Manifests\ModelManifest as ModelManifestImpl;
use Lunar\Core\Manifests\ShippingManifest as ShippingManifestImpl;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Transaction;
use Lunar\Core\Models\Url;
use Lunar\Core\Modifiers\CartLineModifiers;
use Lunar\Core\Modifiers\CartModifiers;
use Lunar\Core\Modifiers\OrderModifiers;
use Lunar\Core\Modifiers\ShippingModifiers;
use Lunar\Core\Observers\AddressObserver;
use Lunar\Core\Observers\AttributeObserver;
use Lunar\Core\Observers\CartLineObserver;
use Lunar\Core\Observers\ChannelObserver;
use Lunar\Core\Observers\CollectionObserver;
use Lunar\Core\Observers\CurrencyObserver;
use Lunar\Core\Observers\CustomerGroupObserver;
use Lunar\Core\Observers\CustomerObserver;
use Lunar\Core\Observers\DiscountObserver;
use Lunar\Core\Observers\FulfilmentLineObserver;
use Lunar\Core\Observers\FulfilmentObserver;
use Lunar\Core\Observers\LanguageObserver;
use Lunar\Core\Observers\MediaObserver;
use Lunar\Core\Observers\OrderLineObserver;
use Lunar\Core\Observers\OrderObserver;
use Lunar\Core\Observers\PriceObserver;
use Lunar\Core\Observers\ProductObserver;
use Lunar\Core\Observers\ProductOptionObserver;
use Lunar\Core\Observers\ProductOptionValueObserver;
use Lunar\Core\Observers\ProductVariantObserver;
use Lunar\Core\Observers\TransactionObserver;
use Lunar\Core\Observers\UrlObserver;
use Lunar\Core\Orders\ReferenceGenerator as OrderReferenceGeneratorImpl;
use Lunar\Core\Pricing\DefaultPriceCalculator;
use Lunar\Core\Pricing\DefaultPriceFormatter;
use Lunar\Core\Pricing\PriceCalculatorInterface;
use Lunar\Core\Pricing\PriceFormatterInterface;
use Lunar\Core\States\Fulfilment\DefaultFulfilmentStateConfig;
use Lunar\Core\Telemetry\Insights as TelemetryInsights;
use Lunar\Core\Telemetry\TelemetryService as TelemetryServiceImpl;
use Lunar\Core\Utils\MeasurementConverter;
use Lunar\Core\Validation\CouponValidator as CouponValidatorImpl;

class LunarServiceProvider extends ServiceProvider
{
    protected $configFiles = [
        'cart',
        'cart_session',
        'database',
        'discounts',
        'fulfilment',
        'media',
        'orders',
        'payments',
        'pricing',
        'products',
        'search',
        'shipping',
        'staff',
        'taxes',
        'urls',
    ];

    protected $root = __DIR__.'/..';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        collect($this->configFiles)->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "lunar.$config");
        });

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunar');

        $this->registerAddonManifest();

        $this->registerServices();
        $this->registerManagers();

        $this->app->register(ActionServiceProvider::class);

        $this->app->terminating(function () {
            if (! app()->runningInConsole()) {
                Telemetry::run();
            }
        });

        Facades\ModelManifest::register();

        $this->app->scoped('lunar-access-control', function (): AccessControlManifest {
            return new AccessControlManifest;
        });

        $this->app->alias('lunar-access-control', AccessControlManifest::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->registerObservers();
        $this->registerBuilderMacros();
        $this->registerBlueprintMacros();

        Facades\ModelManifest::morphMap();

        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("lunar/$config.php"),
                ], 'lunar');
            });

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/lunar'),
            ], 'lunar.translation');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'lunar.migrations');

            $this->commands([
                InstallLunar::class,
                AddonsDiscover::class,
                AddressData::class,
                ScoutIndexerCommand::class,
                SyncNewCustomerOrders::class,
                PruneCarts::class,
            ]);

            if (config('lunar.cart.prune_tables.enabled', false)) {
                $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                    $schedule->command('lunar:prune:carts')->daily();
                });
            }
        }

        Arr::macro('permutate', [Utils\Arr::class, 'permutate']);

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

        Event::listen(OrderPaymentStatusUpdated::class, SendOrderPaymentStatusNotifications::class);
        Event::listen(OrderFulfilmentStatusUpdated::class, SendOrderFulfilmentStatusNotifications::class);
        Event::listen(OrderCancelled::class, SendOrderCancelledNotifications::class);

        $this->registerStaffAuthGuard();
        $this->registerStaffStateListeners();

        Relation::morphMap([
            'staff' => config('lunar.staff.model', Staff::class),
        ]);
    }

    /**
     * Register the staff auth guard + provider.
     */
    protected function registerStaffAuthGuard(): void
    {
        if (! config('lunar.staff.register_guard', true)) {
            return;
        }

        $provider = config('lunar.staff.provider', 'staff');
        $guard = config('lunar.staff.guard', 'staff');
        $model = config('lunar.staff.model', Staff::class);

        $this->app['config']->set("auth.providers.{$provider}", [
            'driver' => 'eloquent',
            'model' => $model,
        ]);

        $this->app['config']->set("auth.guards.{$guard}", [
            'driver' => 'session',
            'provider' => $provider,
        ]);
    }

    /**
     * Register state listeners (e.g. base roles and permissions).
     */
    protected function registerStaffStateListeners(): void
    {
        $states = [
            EnsureBaseRolesAndPermissions::class,
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
     * Bind the stateless services, manifests, registries and value helpers.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(MeasurementConverter::class, function () {
            return new MeasurementConverter;
        });

        $this->app->singleton(CartModifiers::class, function () {
            return new CartModifiers;
        });

        $this->app->singleton(CartLineModifiers::class, function () {
            return new CartLineModifiers;
        });

        $this->app->singleton(OrderModifiers::class, function () {
            return new OrderModifiers;
        });

        $this->app->singleton(ShippingModifiers::class, function () {
            return new ShippingModifiers;
        });

        $this->app->singleton(ShippingManifest::class, function ($app) {
            return $app->make(ShippingManifestImpl::class);
        });

        $this->app->singleton(CarrierManifest::class, function ($app) {
            return $app->make(CarrierManifestImpl::class);
        });

        $this->app->singleton(FulfilmentMethodManifest::class, function ($app) {
            return $app->make(FulfilmentMethodManifestImpl::class);
        });

        $this->app->singleton(AttributeManifest::class, function ($app) {
            return $app->make(AttributeManifestImpl::class);
        });

        $this->app->singleton(FieldTypeManifest::class, function ($app) {
            return $app->make(FieldTypeManifestImpl::class);
        });

        $this->app->singleton(AttributeCache::class, function ($app) {
            return $app->make(AttributeCacheImpl::class);
        });

        $this->app->singleton(ModelManifest::class, function ($app) {
            return $app->make(ModelManifestImpl::class);
        });

        $this->app->singleton(OrderReferenceGenerator::class, function ($app) {
            return $app->make(OrderReferenceGeneratorImpl::class);
        });

        $this->app->singleton(FulfilmentStateConfig::class, function ($app) {
            return $app->make(DefaultFulfilmentStateConfig::class);
        });

        $this->app->bind(PriceFormatterInterface::class, function ($app, array $parameters = []) {
            $concrete = config('lunar.pricing.formatter', DefaultPriceFormatter::class);

            return $app->make($concrete, $parameters);
        });

        $this->app->singleton(PriceCalculatorInterface::class, DefaultPriceCalculator::class);

        $this->app->bind(CouponValidator::class, CouponValidatorImpl::class);

        $this->app->singleton(ProvidesTelemetryInsights::class, function ($app) {
            return $app->make(TelemetryInsights::class);
        });

        $this->app->singleton(TelemetryService::class, function ($app) {
            return $app->make(TelemetryServiceImpl::class);
        });
    }

    /**
     * Bind the manager contracts to their default implementations.
     */
    protected function registerManagers(): void
    {
        $this->app->singleton(CartSession::class, function ($app) {
            return $app->make(CartSessionManager::class);
        });

        $this->app->singleton(StorefrontSession::class, function ($app) {
            return $app->make(StorefrontSessionManager::class);
        });

        $this->app->bind(PricingManager::class, function ($app) {
            return $app->make(PricingManagerImpl::class);
        });

        $this->app->singleton(TaxManager::class, function ($app) {
            return $app->make(TaxManagerImpl::class);
        });

        $this->app->singleton(PaymentManager::class, function ($app) {
            return $app->make(PaymentManagerImpl::class);
        });

        $this->app->singleton(DiscountManager::class, function ($app) {
            return $app->make(DiscountManagerImpl::class);
        });
    }

    protected function registerAddonManifest()
    {
        $this->app->instance(Manifest::class, new Manifest(
            new Filesystem,
            $this->app->basePath(),
            $this->app->bootstrapPath().'/cache/lunar_addons.php'
        ));
    }

    /**
     * Register the observers used in Lunar.
     */
    protected function registerObservers(): void
    {
        Address::observe(AddressObserver::class);
        Attribute::observe(AttributeObserver::class);
        CartLine::observe(CartLineObserver::class);
        Channel::observe(ChannelObserver::class);
        Collection::observe(CollectionObserver::class);
        Currency::observe(CurrencyObserver::class);
        Customer::observe(CustomerObserver::class);
        CustomerGroup::observe(CustomerGroupObserver::class);
        Discount::observe(DiscountObserver::class);
        Fulfilment::observe(FulfilmentObserver::class);
        FulfilmentLine::observe(FulfilmentLineObserver::class);
        Language::observe(LanguageObserver::class);
        Order::observe(OrderObserver::class);
        OrderLine::observe(OrderLineObserver::class);
        Price::observe(PriceObserver::class);
        Product::observe(ProductObserver::class);
        ProductOption::observe(ProductOptionObserver::class);
        ProductOptionValue::observe(ProductOptionValueObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
        Transaction::observe(TransactionObserver::class);
        Url::observe(UrlObserver::class);

        if ($mediaModel = config('media-library.media_model')) {
            $mediaModel::observe(MediaObserver::class);
        }
    }

    protected function registerBuilderMacros(): void
    {
        Builder::macro('orderBySequence', function (iterable $ids) {
            /** @var Builder $this */
            $driver = $this->getConnection()->getDriverName();

            if (blank($ids)) {
                return $this;
            }

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                return $this->orderByRaw("FIELD(id, {$placeholders})", $ids);
            }

            if ($driver === 'pgsql') {
                $orderCases = '';
                foreach ($ids as $index => $id) {
                    $orderCases .= "WHEN id = $id THEN $index ";
                }

                return $this->orderByRaw("CASE $orderCases ELSE ".count($ids).' END');
            }

            return $this;
        });
    }

    /**
     * Register the blueprint macros.
     */
    protected function registerBlueprintMacros(): void
    {
        Blueprint::macro('scheduling', function () {
            /** @var Blueprint $this */
            $this->boolean('enabled')->default(false)->index();
            $this->timestamp('starts_at')->nullable()->index();
            $this->timestamp('ends_at')->nullable()->index();
        });

        Blueprint::macro('dimensions', function () {
            /** @var Blueprint $this */
            $columns = ['length', 'width', 'height', 'weight', 'volume'];
            $unitDefaults = [
                'weight' => 'kg',
            ];

            foreach ($columns as $column) {
                $this->decimal("{$column}_value", 10, 4)->default(0)->nullable()->index();
                $this->string("{$column}_unit")
                    ->default($unitDefaults[$column] ?? 'mm')
                    ->nullable();
            }
        });

        Blueprint::macro('userForeignKey', function ($field_name = 'user_id', $nullable = false) {
            /** @var Blueprint $this */
            $userModel = config('auth.providers.users.model');

            $type = config('lunar.database.users_id_type', 'bigint');

            if ($type == 'uuid') {
                $this->foreignUuid($field_name)
                    ->nullable($nullable)
                    ->constrained(
                        (new $userModel)->getTable()
                    );
            } elseif ($type == 'int') {
                $this->unsignedInteger($field_name)->nullable($nullable);
                $this->foreign($field_name)->references('id')->on('users');
            } else {
                $this->foreignId($field_name)
                    ->nullable($nullable)
                    ->constrained(
                        (new $userModel)->getTable()
                    );
            }
        });
    }
}
