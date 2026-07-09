<?php

namespace Lunar\Panel;

use Illuminate\Support\ServiceProvider;
use Lunar\Panel\Console\Commands\LinkPanelAssetsCommand;
use Lunar\Panel\Navigation\NavigationItem;

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
            ], ['panel-assets', 'panel-all-assets']);

            $this->commands([LinkPanelAssetsCommand::class]);
        }

        $this->app->booted(function (): void {
            $this->processRegisteredSections();
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
