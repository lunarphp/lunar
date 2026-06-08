<?php

namespace Lunar\Checkout;

use Illuminate\Support\ServiceProvider;
use Lunar\Checkout\DataObjects\CheckoutTheme;

class CheckoutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Default theme. A consumer re-brands the checkout by binding their own
        // CheckoutTheme in a service provider — config never selects the theme.
        $this->app->bind(CheckoutTheme::class, fn () => CheckoutTheme::tender());
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

        // The Vue component source — the consumer's Vite compiles it and Inertia
        // resolves the page. Re-run with --force on upgrade.
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/lunar-checkout'),
        ], 'lunar.checkout.source');

        // Prebuilt, plain (sandboxed) CSS. No build step — the consumer imports
        // and their Vite fingerprints it.
        $this->publishes([
            __DIR__.'/../resources/css' => resource_path('css/vendor/lunar-checkout'),
        ], 'lunar.checkout.styles');
    }
}
