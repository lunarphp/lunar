<?php

namespace Lunar\Panel;

use Illuminate\Support\ServiceProvider;

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
        }
    }
}
