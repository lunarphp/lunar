<?php

namespace Lunar\Checkout;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Session\Session as LaravelSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Inertia\ResponseFactory;
use Inertia\Ssr\ExcludesSsrPaths;
use Inertia\Ssr\Gateway;
use Lunar\Checkout\Console\Commands\ExpireCheckoutSessions;
use Lunar\Checkout\Console\Commands\ReconcileCheckoutSessions;
use Lunar\Checkout\Contracts\AddressLookup;
use Lunar\Checkout\Contracts\CheckoutAssets as CheckoutAssetsContract;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\CheckoutSessionStateConfig;
use Lunar\Checkout\Contracts\ElementDataStore;
use Lunar\Checkout\Contracts\ElementRegistry as ElementRegistryContract;
use Lunar\Checkout\Contracts\PaymentMethodRegistry as PaymentMethodRegistryContract;
use Lunar\Checkout\DataObjects\CheckoutTheme;
use Lunar\Checkout\Exceptions\CheckoutSessionConflictException;
use Lunar\Checkout\Exceptions\CheckoutSessionNotOperableException;
use Lunar\Checkout\Listeners\CompleteSessionOnPaymentSuccess;
use Lunar\Checkout\Managers\AddressLookupManager;
use Lunar\Checkout\Managers\CheckoutSessionManager;
use Lunar\Checkout\Session\SessionElementStore;
use Lunar\Checkout\Shipping\PickupPointModifier;
use Lunar\Checkout\States\CheckoutSession\DefaultCheckoutSessionStateConfig;
use Lunar\Checkout\Support\CheckoutAssets;
use Lunar\Checkout\Validation\Cart\PickupPointRequired;
use Lunar\Core\Events\PaymentAttemptEvent;
use Lunar\Core\Modifiers\ShippingModifiers;

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

        // Payment-method registry (spec 0002 §B). Core ships no methods —
        // gateway packages or the host app register them in boot(); with none
        // registered the payment region projects empty.
        $this->app->singleton(PaymentMethodRegistryContract::class, fn ($app) => new PaymentMethodRegistry($app));

        // Element data store. This session-backed binding serves the embedded
        // flow; the uuid checkout flow swaps in a row-backed store per request.
        $this->app->scoped(
            ElementDataStore::class,
            fn ($app) => new SessionElementStore($app->make(LaravelSession::class)),
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

        // Address lookup (spec 0011 §B): same Manager shape as the checkout
        // driver. Selected by config value; hosts extend() their own.
        $this->app->singleton(AddressLookupManager::class);
        $this->app->bind(
            AddressLookup::class,
            fn ($app) => $app->make(AddressLookupManager::class)->driver(),
        );
    }

    public function boot(): void
    {
        RateLimiter::for('checkout-contact-lookup', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));

        // Same shape as the contact bucket. An ungated lookup endpoint on a
        // per-lookup-billed vendor is a way for a stranger to spend the
        // merchant's money.
        RateLimiter::for('checkout-address-lookup', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));

        // Same shape again: the quote endpoint runs the real driver writes
        // (rolled back) for a candidate address, so it is throttled like the
        // other lookups rather than left open to abuse.
        RateLimiter::for('checkout-shipping-quote', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));

        $this->mergeConfigFrom(__DIR__.'/../config/checkout.php', 'lunar.checkout');

        // Spec 0013 §C: the chosen collection point rides on the collect
        // option's meta so core stamps it onto the shipping order line.
        $this->app->make(ShippingModifiers::class)->add(PickupPointModifier::class);

        // Spec 0013 §D: order creation refuses a collect cart with no chosen
        // point through Lunar's own validator seam, so canCreateOrder() is
        // the authority for every caller, not only this package's pay boundary.
        config([
            'lunar.cart.validators.order_create' => array_values(array_unique([
                ...config('lunar.cart.validators.order_create', []),
                PickupPointRequired::class,
            ])),
        ]);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunar-checkout');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunar-checkout');

        // The gateway-agnostic success bridge (spec 0002 §D): any gateway's
        // authorize() success completes the paying session for its cart.
        Event::listen(PaymentAttemptEvent::class, CompleteSessionOnPaymentSuccess::class);

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

        $this->excludeFromServerSideRendering();

        $this->registerExceptionRenderers();

        $this->registerPublishing();
    }

    /**
     * Map the domain's state-refusal exceptions onto transport responses with
     * customer-facing copy (spec 0003 §H). Without this they surface as 500s
     * whose message is the developer reason code ("Checkout session conflict
     * [frozen]."). The machine code stays available in the payload as
     * `reason` for clients that branch on it.
     */
    private function registerExceptionRenderers(): void
    {
        $this->callAfterResolving(ExceptionHandler::class, function (ExceptionHandler $handler): void {
            if (! method_exists($handler, 'renderable')) {
                return;
            }

            $handler->renderable(function (CheckoutSessionConflictException $e, Request $request) {
                if (! $request->expectsJson()) {
                    return null;
                }

                return response()->json([
                    'message' => 'This checkout is busy finishing a payment attempt. Wait a moment and try again.',
                    'reason' => $e->reason,
                ], 409);
            });

            $handler->renderable(function (CheckoutSessionNotOperableException $e, Request $request) {
                if (! $request->expectsJson()) {
                    return null;
                }

                return response()->json([
                    'message' => 'This checkout session has ended. Return to your basket to start again.',
                    'reason' => 'not_operable',
                ], 410);
            });
        });
    }

    /**
     * Exclude the checkout's paths from the host application's Inertia SSR.
     *
     * The checkout is a self-contained Inertia app (spec 0008): its own root
     * view, its own bundle, and page components that ship inside this package.
     * A host running SSR renders from its own entry, whose page map covers only
     * the host's own pages, so an SSR attempt on a checkout render can do
     * nothing but fail ("Page not found: Show") and fall back to a client
     * render, costing a wasted round trip and a misleading error per view.
     *
     * Guarded rather than assumed: `withoutSsr()` arrived in Inertia v3 and the
     * host may have swapped the gateway for one that cannot exclude paths.
     */
    private function excludeFromServerSideRendering(): void
    {
        if (! method_exists(ResponseFactory::class, 'withoutSsr')) {
            return;
        }

        if (! $this->app->make(Gateway::class) instanceof ExcludesSsrPaths) {
            return;
        }

        $path = trim((string) config('lunar.checkout.path', 'checkout'), '/');

        Inertia::withoutSsr([$path, $path.'/*']);
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
