<?php

namespace Lunar\Paypal;

use Illuminate\Support\ServiceProvider;
use Lunar\Core\Facades\Payments;
use Lunar\Paypal\Contracts\PaypalInterface;

class PaypalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/paypal.php', 'lunar.paypal');

        // Scoped, not singleton: the client memoizes nothing across requests, but
        // a long-lived worker should not hold one built from another request's
        // resolved config.
        $this->app->scoped(PaypalInterface::class, function ($app) {
            return $app->make(Paypal::class);
        });
    }

    public function boot(): void
    {
        Payments::extend('paypal', function ($app) {
            return $app->make(PaypalPaymentType::class);
        });

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishes([
            __DIR__.'/../config/paypal.php' => config_path('lunar/paypal.php'),
        ], 'lunar.paypal.config');
    }
}
