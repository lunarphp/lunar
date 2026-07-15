<?php

namespace LunarPanelExample;

use Illuminate\Support\ServiceProvider;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\PanelManager;

class ExampleAddonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Panel::section(new ExampleSection);

        $this->app->make(PanelManager::class)->vite('example-addon', [
            'input' => 'resources/js/addon.ts',
            'hotFile' => null,
            'buildDirectory' => 'vendor/lunar-panel/example-addon',
        ]);
    }
}
