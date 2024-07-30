<?php

namespace Lunar\Paypal;

use Illuminate\Support\ServiceProvider;
use Lunar\Facades\Payments;

class PaypalServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Register our payment type.
        Payments::extend('paypal', function ($app) {
            return $app->make(PaypalPaymentType::class);
        });

        $this->app->singleton(PaypalInterface::class, function ($app) {
            return $app->make(Paypal::class);
        });

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
