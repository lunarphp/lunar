<?php

namespace Lunar\Checkout;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Session\Session as LaravelSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Lunar\Checkout\Console\Commands\ExpireCheckoutSessions;
use Lunar\Checkout\Console\Commands\ReconcileCheckoutSessions;
use Lunar\Checkout\Contracts\CheckoutAssets as CheckoutAssetsContract;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\CheckoutSession as CheckoutSessionContract;
use Lunar\Checkout\Contracts\CheckoutSessionStateConfig;
use Lunar\Checkout\Contracts\ElementRegistry as ElementRegistryContract;
use Lunar\Checkout\DataObjects\CheckoutTheme;
use Lunar\Checkout\Managers\CheckoutSessionManager;
use Lunar\Checkout\Session\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\DefaultCheckoutSessionStateConfig;
use Lunar\Checkout\Support\CheckoutAssets;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * Absolute path to the checkout app's Vite dev hot file. The package's own
     * `npm run dev` writes it here; it is symlinked into the consumer's vendor/
     * dir, so the root view can read it directly. Present → Laravel's Vite class
     * serves the app from the dev server (HMR); absent → the published build.
     */
    public static function appHotFile(): string
    {
        return __DIR__.'/../resources/dist/hot';
    }

    /** Public build dir (relative to public/) the app is published to. */
    public static function appBuildDirectory(): string
    {
        return 'vendor/lunarphp/checkout/build';
    }

    public function register(): void
    {
        // Action contract bindings (spec 0016): the canonical swappable seams.
        $this->app->register(ActionServiceProvider::class);

        // Default theme. A consumer re-brands the checkout by binding their own
        // CheckoutTheme in a service provider — config never selects the theme.
        $this->app->bind(CheckoutTheme::class, fn () => CheckoutTheme::tender());

        // Element registry — a single build-time instance consumers register
        // elements onto (via the Checkout facade). The container is the swap
        // seam; rebind the contract to substitute the implementation.
        $this->app->singleton(ElementRegistryContract::class, fn ($app) => new ElementRegistry($app));

        // Contributed-asset registry (spec 0009). Build-time + Octane-safe like
        // the element registry: packages call CheckoutAssets::register() in their
        // own provider to contribute an element/gateway chunk into the prebuilt
        // app at runtime — no fork, no rebuild, no publish of the app's assets.
        $this->app->singleton(CheckoutAssetsContract::class, fn () => new CheckoutAssets);

        // Checkout session (prototype) — request-scoped value store backing the
        // data elements capture. Swapped for the spec 0004 model by rebinding.
        $this->app->scoped(
            CheckoutSessionContract::class,
            fn ($app) => new CheckoutSession($app->make(LaravelSession::class)),
        );

        // Checkout-session state machine catalogue (spec 0004 §C). Bound in
        // register() so the machine is configured before any model casts the
        // status — Octane-safe, no runtime rebind.
        $this->app->bind(CheckoutSessionStateConfig::class, DefaultCheckoutSessionStateConfig::class);

        // The checkout driver (spec 0004): the Manager resolves the active
        // driver by name from config('lunar.checkout.driver'); the contract
        // resolves to that driver. Swap by config value or extend() — not a
        // class-swap config key (Lunar convention).
        $this->app->singleton(CheckoutSessionManager::class);
        $this->app->bind(
            CheckoutDriver::class,
            fn ($app) => $app->make(CheckoutSessionManager::class)->driver(),
        );
    }

    public function boot(): void
    {
        RateLimiter::for('checkout-contact-lookup', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));

        $this->mergeConfigFrom(__DIR__.'/../config/checkout.php', 'lunar.checkout');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunar-checkout');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunar-checkout');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExpireCheckoutSessions::class,
                ReconcileCheckoutSessions::class,
            ]);

            $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
                $schedule->command('lunar:checkout:expire-sessions')->hourly();

                // Bounded PaymentProcessing reconciliation (spec 0010 §F).
                $schedule->command('lunar:checkout:reconcile')
                    ->everyFifteenMinutes()
                    ->withoutOverlapping();
            });
        }

        // Package routes are opt-out: a publish-and-own consumer disables them
        // (config lunar.checkout.routes => false) and registers their own.
        if (config('lunar.checkout.routes', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        $this->registerPublishing();
    }

    private function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/checkout.php' => config_path('lunar/checkout.php'),
        ], 'lunar.checkout.config');

        // The checkout app's prebuilt bundle. Published to the host's public/
        // so Laravel's Vite class serves it same-origin (spec 0008 §B) — the
        // same publish-to-public model addons use. Re-run with --force after a
        // package upgrade. `php artisan vendor:publish --tag=lunar-checkout-assets`.
        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/lunarphp/checkout'),
        ], 'lunar-checkout-assets');

        // The Inertia ROOT view (spec 0008 §C). Publish to customise the shell
        // (meta, fonts) without owning the whole app.
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar-checkout'),
        ], 'lunar.checkout.views');

        // Publish-and-own (spec 0008 §C): the whole self-contained app — Vue
        // source, CSS, AND its own build toolchain. The consumer disables the
        // package route, registers their own, edits the components, and runs the
        // app's OWN Vite (`npm run build`) — not their storefront's bundler.
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('vendor/lunar-checkout/resources/js'),
            __DIR__.'/../resources/css' => resource_path('vendor/lunar-checkout/resources/css'),
            __DIR__.'/../package.json' => resource_path('vendor/lunar-checkout/package.json'),
            __DIR__.'/../vite.config.js' => resource_path('vendor/lunar-checkout/vite.config.js'),
        ], 'lunar.checkout.source');
    }
}
