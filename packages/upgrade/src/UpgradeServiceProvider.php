<?php

declare(strict_types=1);

namespace Lunar\Upgrade;

use Illuminate\Support\ServiceProvider;
use Lunar\Upgrade\Console\UpgradeCommand;
use Lunar\Upgrade\Support\ClassStringRewriter;
use Lunar\Upgrade\Support\SchemaGuard;
use Lunar\Upgrade\Support\VersionGuard;

class UpgradeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/upgrade.php', 'lunar.upgrade');

        $this->app->singleton(VersionGuard::class);
        $this->app->singleton(SchemaGuard::class);
        $this->app->singleton(ClassStringRewriter::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                UpgradeCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/upgrade.php' => config_path('lunar/upgrade.php'),
            ], 'lunar.upgrade.config');
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunar-upgrade');
    }
}
