<?php

namespace Lunar\Stripe;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Facades\Payments;
use Lunar\Stripe\Components\PaymentForm;
use Lunar\Stripe\Managers\StripeManager;

class StripePaymentsServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Register our payment type.
        Payments::extend('stripe', function ($app) {
            return $app->make(StripePaymentType::class);
        });

        $this->app->singleton('gc:stripe', function ($app) {
            return $app->make(StripeManager::class);
        });

        Blade::directive('stripeScripts', function () {
            return  <<<'EOT'
                <script src="https://js.stripe.com/v3/"></script>
            EOT;
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunar');

        $this->mergeConfigFrom(__DIR__.'/../config/stripe.php', 'lunar.stripe');

        $this->publishes([
            __DIR__.'/../config/stripe.php' => config_path('lunar/stripe.php'),
        ], 'lunar.stripe.config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar'),
        ], 'lunar.stripe.components');

        // Register the stripe payment component.
        Livewire::component('stripe.payment', PaymentForm::class);
    }
}
