<?php

namespace Lunar\Stripe;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Stripe\Actions\ConstructWebhookEvent;
use Lunar\Stripe\Components\PaymentForm;
use Lunar\Stripe\Concerns\ConstructsWebhookEvent;
use Lunar\Stripe\Managers\StripeManager;
use Lunar\Stripe\Models\StripePaymentIntent;

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

        Cart::resolveRelationUsing('paymentIntents', function (Cart $cart) {
            return $cart->hasMany(StripePaymentIntent::class);
        });

        $this->app->bind(ConstructsWebhookEvent::class, function ($app) {
            return $app->make(ConstructWebhookEvent::class);
        });

        $this->app->singleton('lunar:stripe', function ($app) {
            return $app->make(StripeManager::class);
        });

        Blade::directive('stripeScripts', function () {
            return <<<'EOT'
                <script src="https://js.stripe.com/v3/"></script>
            EOT;
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunar');
        $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->mergeConfigFrom(__DIR__.'/../config/stripe.php', 'lunar.stripe');

        $this->publishes([
            __DIR__.'/../config/stripe.php' => config_path('lunar/stripe.php'),
        ], 'lunar.stripe.config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar'),
        ], 'lunar.stripe.components');

        if (class_exists(Livewire::class)) {
            // Register the stripe payment component.
            Livewire::component('stripe.payment', PaymentForm::class);
        }
    }
}
