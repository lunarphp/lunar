<?php

namespace Lunar\Checkout;

use Illuminate\Support\ServiceProvider;

class CheckoutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunar-checkout');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->mergeConfigFrom(__DIR__.'/../config/checkout.php', 'lunar.checkout');

        $this->publishes([
            __DIR__.'/../config/checkout.php' => config_path('lunar/checkout.php'),
        ], 'lunar.checkout.config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar-checkout'),
        ], 'lunar.checkout.views');
    }
}
