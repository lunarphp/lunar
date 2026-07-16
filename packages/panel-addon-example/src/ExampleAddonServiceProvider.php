<?php

namespace LunarPanelExample;

use Illuminate\Support\ServiceProvider;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\PanelManager;

class ExampleAddonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // The namespace registered here is what Section::langNamespaces() opts
        // in to serving through the panel's translations endpoint.
        $this->loadTranslationsFrom(dirname(__DIR__).'/resources/lang', 'example-addon');

        Panel::section(new ExampleSection);

        $this->app->make(PanelManager::class)->vite('example-addon', [
            'input' => 'resources/js/addon.ts',
            'hotFile' => null,
            'buildDirectory' => 'vendor/lunar-panel/example-addon',
            // Lets `php artisan lunar:panel:link` symlink this package's
            // compiled build/ into public/vendor/lunar-panel/example-addon.
            '__buildSourcePath' => dirname(__DIR__).'/build',
        ]);
    }
}
