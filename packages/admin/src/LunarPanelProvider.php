<?php

namespace Lunar\Admin;

use Filament\Support\Events\FilamentUpgraded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Console\Commands\MakeLunarAdminCommand;
use Lunar\Admin\Console\Commands\PublishAdminResourcesCommand;
use Lunar\Admin\Events\CustomerUserEdited;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Listeners\FilamentUpgradedListener;
use Lunar\Admin\Support\ActivityLog\Manifest as ActivityLogManifest;

class LunarPanelProvider extends ServiceProvider
{
    protected $configFiles = [
        'panel',
    ];

    protected $root = __DIR__.'/..';

    public function register(): void
    {
        $this->app->scoped('lunar-panel', function (): LunarPanelManager {
            return new LunarPanelManager;
        });

        $this->app->scoped('lunar-activity-log', function (): ActivityLogManifest {
            return new ActivityLogManifest;
        });

        // 'lunar-access-control' binding now lives in Lunar\Core\LunarServiceProvider.
        // 'lunar-attribute-data' binding lives in LunarFilamentServiceProvider.
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunarpanel');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunarpanel');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunarpanel'),
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/lunarpanel'),
        ]);

        $this->publishes([
            __DIR__.'/../resources/views/pdf' => resource_path('views/vendor/lunarpanel/pdf'),
        ], 'lunarpanel.pdf');

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
                PublishAdminResourcesCommand::class,
            ]);
        }

        // Catalog reindexing rides the core cache-invalidation events
        // (Lunar\Core\Listeners\ReindexOnCacheInvalidation). A linked user's
        // email is part of the customer search document, so a user edit still
        // reindexes the customer here.
        Event::listen(
            CustomerUserEdited::class,
            fn ($event) => sync_with_search($event->model),
        );

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/lunarpanel'),
        ], 'public');

        $this->registerPermissionManifest();
        $this->registerLunarSynthesizer();
        $this->registerBridgeRecordUrls();
        // $this->registerUpgradedListener();
    }

    /**
     * Point the bridge's record-URL resolvers at the admin shell's pages.
     *
     * Bridge tables/widgets call `RecordUrls::for(...)` to link out to a
     * record's management page. Without this binding the link is omitted —
     * which is the correct behaviour for downstream panels that don't ship
     * the admin shell.
     */
    protected function registerBridgeRecordUrls(): void
    {
        $this->app['config']->set('lunar-filament.record_urls.order',
            fn ($record, array $context = []) => ManageOrder::getUrl([...$context, 'record' => $record]),
        );

        $this->app['config']->set('lunar-filament.record_urls.product_variant',
            fn ($record, array $context = []) => ProductVariantResource::getUrl('edit', [...$context, 'record' => $record]),
        );

        $this->app['config']->set('lunar-filament.record_urls.collection_edit',
            fn ($record, array $context = []) => CollectionResource::getUrl('edit', [...$context, 'record' => $record]),
        );
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

    protected function registerLunarSynthesizer(): void
    {
        // The bridge (lunarphp/filament) now owns synthesizer registration via
        // LunarFilamentServiceProvider::registerSynthesizers(). Kept as a no-op
        // for v2 in case downstream code overrides this method; removed in v3.
    }
}
