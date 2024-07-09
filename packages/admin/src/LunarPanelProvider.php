<?php

namespace Lunar\Admin;

use Filament\Support\Events\FilamentUpgraded;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Admin\Auth\Manifest;
use Lunar\Admin\Console\Commands\MakeLunarAdminCommand;
use Lunar\Admin\Database\State\EnsureBaseRolesAndPermissions;
use Lunar\Admin\Events\ChildCollectionCreated;
use Lunar\Admin\Events\CollectionProductDetached;
use Lunar\Admin\Events\CustomerAddressEdited;
use Lunar\Admin\Events\CustomerUserEdited;
use Lunar\Admin\Events\ModelChannelsUpdated;
use Lunar\Admin\Events\ModelPricesUpdated;
use Lunar\Admin\Events\ModelUrlsUpdated;
use Lunar\Admin\Events\ProductAssociationsUpdated;
use Lunar\Admin\Events\ProductCollectionsUpdated;
use Lunar\Admin\Events\ProductCustomerGroupsUpdated;
use Lunar\Admin\Events\ProductPricingUpdated;
use Lunar\Admin\Events\ProductVariantOptionsUpdated;
use Lunar\Admin\Listeners\FilamentUpgradedListener;
use Lunar\Admin\Models\Staff;
use Lunar\Admin\Support\ActivityLog\Manifest as ActivityLogManifest;
use Lunar\Admin\Support\Forms\AttributeData;
use Lunar\Admin\Support\Synthesizers\PriceSynth;

class LunarPanelProvider extends ServiceProvider
{
    protected $configFiles = [
        'panel',
    ];

    protected $root = __DIR__.'/..';

    public function register(): void
    {
        $this->app->scoped('lunar-panel', function (): LunarPanelManager {
            return new LunarPanelManager();
        });

        $this->app->scoped('lunar-access-control', function (): Manifest {
            return new Manifest();
        });

        $this->app->scoped('lunar-activity-log', function (): ActivityLogManifest {
            return new ActivityLogManifest();
        });

        $this->app->scoped('lunar-attribute-data', function (): AttributeData {
            return new AttributeData();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunarpanel');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunarpanel');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunarpanel'),
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/lunarpanel'),
        ]);

        collect($this->configFiles)->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "lunar.$config");
        });

        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("lunar/$config.php"),
                ], 'lunar');
            });

            $this->commands([
                MakeLunarAdminCommand::class,
            ]);
        }

        Event::listen([
            ChildCollectionCreated::class,
            CollectionProductDetached::class,
            CustomerAddressEdited::class,
            CustomerUserEdited::class,
            ProductAssociationsUpdated::class,
            ProductCollectionsUpdated::class,
            ProductPricingUpdated::class,
            ProductCustomerGroupsUpdated::class,
            ProductVariantOptionsUpdated::class,
            ModelChannelsUpdated::class,
            ModelPricesUpdated::class,
            ModelUrlsUpdated::class,
        ], fn ($event) => sync_with_search($event->model));

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/lunarpanel'),
        ], 'public');

        $this->registerAuthGuard();
        $this->registerPermissionManifest();
        $this->registerStateListeners();
        $this->registerLunarSynthesizer();
        // $this->registerUpgradedListener();
    }

    /**
     * Register our auth guard.
     */
    protected function registerAuthGuard(): void
    {
        $this->app['config']->set('auth.providers.staff', [
            'driver' => 'eloquent',
            'model' => Staff::class,
        ]);

        $this->app['config']->set('auth.guards.staff', [
            'driver' => 'session',
            'provider' => 'staff',
        ]);
    }

    /**
     * Register our permissions manifest.
     */
    protected function registerPermissionManifest(): void
    {
        Gate::after(function ($user, $ability) {
            // Are we trying to authorize something within the admin panel?
            $permission = $this->app->get('lunar-access-control')->getPermissions()->first(fn ($permission) => $permission->handle === $ability);
            if ($permission) {
                return $user->admin || $user->hasPermissionTo($ability);
            }
        });
    }

    protected function registerUpgradedListener(): void
    {
        Event::listen(FilamentUpgraded::class, FilamentUpgradedListener::class);
    }

    protected function registerStateListeners()
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

    protected function registerLunarSynthesizer(): void
    {
        \Lunar\Admin\Support\Facades\AttributeData::synthesizeLivewireProperties();
        Livewire::propertySynthesizer(PriceSynth::class);
    }
}
