<?php

namespace Lunar\DemoData;

use Illuminate\Support\ServiceProvider;
use Lunar\DemoData\Console\Commands\DemoCommand;

class DemoDataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/demo-data.php', 'lunar.demo-data');
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            DemoCommand::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/demo-data.php' => config_path('lunar/demo-data.php'),
        ], 'lunar.demo-data');
    }
}
