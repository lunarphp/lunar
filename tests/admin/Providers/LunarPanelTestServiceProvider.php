<?php

namespace Lunar\Tests\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\LunarPanel;

class LunarPanelTestServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        LunarPanel::register();
    }
}
