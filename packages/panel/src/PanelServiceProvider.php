<?php

namespace Lunar\Panel;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\Auth\EmailTwoFactor;
use Lunar\Panel\Console\Commands\LinkPanelAssetsCommand;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\Http\Middleware\Authenticate;
use Lunar\Panel\Http\Middleware\HandlePanelInertiaRequests;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Sections\Sales\SalesSection;
use Lunar\Panel\Sections\Settings\ChannelsSection;

class PanelServiceProvider extends ServiceProvider
{
    /** @var string[] */
    protected $configFiles = [
        'panel',
    ];

    protected $root = __DIR__.'/..';

    public function register(): void
    {
        collect($this->configFiles)->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "lunar.$config");
        });

        $this->app->singleton(PanelManager::class, fn (): PanelManager => new PanelManager);

        $this->app->singleton(AppAuthentication::class);
        $this->app->singleton(EmailTwoFactor::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom("{$this->root}/resources/views", 'panel');
        $this->loadTranslationsFrom("{$this->root}/resources/lang", 'panel');

        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("lunar/$config.php"),
                ], 'lunar');
            });

            $this->publishes([
                "{$this->root}/public/build" => public_path('vendor/lunar-panel/build'),
                "{$this->root}/public/favicons" => public_path('vendor/lunar-panel/favicons'),
            ], ['panel-assets', 'panel-all-assets']);

            $this->commands([LinkPanelAssetsCommand::class]);
        }

        $this->registerPermissionGate();

        Panel::section(new SalesSection);
        Panel::section(new ChannelsSection);

        $this->app->booted(function (): void {
            $this->processRegisteredSections();
            $this->registerRoutes();
        });
    }

    /**
     * Grant manifest permissions to admin staff or explicit permission holders.
     * Mirrors the Filament admin's gate so either panel works standalone.
     */
    protected function registerPermissionGate(): void
    {
        Gate::after(function ($user, string $ability) {
            $permission = $this->app->get('lunar-access-control')
                ->getPermissions()
                ->first(fn ($permission) => $permission->handle === $ability);

            if ($permission) {
                return $user->admin || $user->hasPermissionTo($ability);
            }
        });
    }

    protected function registerRoutes(): void
    {
        $manager = $this->app->make(PanelManager::class);
        $prefix = $manager->path();
        $middleware = config('lunar.panel.route_middleware', ['web']);

        Route::middleware([...$middleware, HandlePanelInertiaRequests::class])
            ->prefix($prefix)
            ->group("{$this->root}/routes/auth.php");

        Route::middleware([...$middleware, Authenticate::class, HandlePanelInertiaRequests::class])
            ->prefix($prefix)
            ->group(function () use ($manager): void {
                $this->loadRoutesFrom("{$this->root}/routes/web.php");

                foreach ($manager->getRouteRegistrars() as $registrar) {
                    $registrar();
                }
            });
    }

    protected function processRegisteredSections(): void
    {
        $manager = $this->app->make(PanelManager::class);

        $manager->navigation()->addTopLevelItem(new NavigationItem(
            key: 'dashboard',
            label: 'panel::nav.dashboard',
            icon: 'layout-dashboard',
            route: 'panel.dashboard',
            priority: 0,
            exact: true,
        ));

        $manager->processSections();
    }
}
