<?php

namespace Lunar\Tests\Panel\Fixtures\Addon;

use Illuminate\Support\ServiceProvider;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\PanelManager;

class AddonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(dirname(__DIR__).'/resources/lang', 'widgets-addon');

        Panel::section(new WidgetsSection);
        Panel::extendSection(new WidgetsSectionExtension);

        $this->app->make(PanelManager::class)->vite('widgets-addon', [
            'input' => 'resources/js/app.ts',
            'hotFile' => null,
            'buildDirectory' => 'vendor/lunar-panel/widgets-addon',
            '__buildSourcePath' => dirname(__DIR__).'/resources/build',
        ]);
    }
}
