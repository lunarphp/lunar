<?php

namespace Lunar\Api;

use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Lunar\Api\Admin\Auth\Abilities;
use Lunar\Api\Admin\Auth\ApiKeyGuard;
use Lunar\Api\Admin\Http\Middleware\AuthenticateApiKey;
use Lunar\Api\Admin\Resources\V1 as AdminV1;
use Lunar\Api\Console\ApiKeyCommand;
use Lunar\Api\Console\SchemaCommand;
use Lunar\Api\Contracts\ApiManager as ApiManagerContract;
use Lunar\Api\Contracts\CartTokenCodec;
use Lunar\Api\Contracts\CustomerResolver;
use Lunar\Api\Http\Controllers\SchemaController;
use Lunar\Api\Http\Exceptions\ErrorRenderer;
use Lunar\Api\Http\Middleware\EnforceJson;
use Lunar\Api\Models\ApiKey;
use Lunar\Api\Query\QueryParser;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Storefront\Http\Middleware\ResolveCart;
use Lunar\Api\Storefront\Http\Middleware\ResolveStorefrontContext;
use Lunar\Api\Storefront\Resources\V1 as StorefrontV1;
use Lunar\Api\Support\HmacCartTokenCodec;
use Lunar\Api\Support\LatestCustomerResolver;
use Throwable;

class ApiServiceProvider extends ServiceProvider
{
    protected string $root = __DIR__.'/..';

    public function register(): void
    {
        $this->mergeConfigFrom("{$this->root}/config/api.php", 'lunar.api');

        $this->app->singleton(ApiManagerContract::class, fn ($app): ApiManager => new ApiManager($app));

        $this->app->bind(QueryParser::class, fn ($app): QueryParser => new QueryParser(
            (int) $app['config']->get('lunar.api.pagination.max_include_depth', 3),
        ));

        $this->app->singleton(CartTokenCodec::class, fn ($app): HmacCartTokenCodec => new HmacCartTokenCodec(
            (string) $app['config']->get('app.key'),
            (int) $app['config']->get('lunar.api.storefront.cart_token_ttl_days', 30),
        ));

        $this->app->singleton(CustomerResolver::class, LatestCustomerResolver::class);

        $this->app->singleton(ErrorRenderer::class, fn ($app): ErrorRenderer => new ErrorRenderer(
            (bool) $app['config']->get('app.debug', false),
        ));
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom("{$this->root}/resources/lang", 'api');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom("{$this->root}/database/migrations");
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                "{$this->root}/config/api.php" => config_path('lunar/api.php'),
            ], ['lunar', 'api-config']);

            $this->commands([
                ApiKeyCommand::class,
                SchemaCommand::class,
            ]);
        }

        Relation::morphMap([
            'api_key' => ApiKey::class,
        ]);

        $this->registerAdminGuard();
        Abilities::registerGate();
        $this->registerRateLimiters();
        $this->registerExceptionRenderer();
        $this->registerBuiltInResources();

        $this->app->booted(function (): void {
            $this->validateDefinitions();
            $this->registerRoutes();
        });
    }

    protected function api(): ApiManagerContract
    {
        return $this->app->make(ApiManagerContract::class);
    }

    protected function registerBuiltInResources(): void
    {
        $this->api()->storefront('v1')->resource(
            StorefrontV1\ProductResource::class,
            StorefrontV1\ProductVariantResource::class,
            StorefrontV1\ProductOptionValueResource::class,
            StorefrontV1\BrandResource::class,
            StorefrontV1\CollectionResource::class,
            StorefrontV1\CollectionGroupResource::class,
            StorefrontV1\UrlResource::class,
            StorefrontV1\CartResource::class,
            StorefrontV1\CartLineResource::class,
            StorefrontV1\CustomerResource::class,
        );

        $this->api()->admin('v1')->resource(
            AdminV1\ProductResource::class,
            AdminV1\ProductVariantResource::class,
            AdminV1\PriceResource::class,
            AdminV1\BrandResource::class,
            AdminV1\ApiKeyResource::class,
        );
    }

    /**
     * Build every definition once the app has booted so a duplicate field or
     * a mis-targeted extension fails the process, not the first request.
     */
    protected function validateDefinitions(): void
    {
        foreach ($this->api()->surfaces() as $registry) {
            $registry->definitions();
        }
    }

    /**
     * The `lunar-api-key` driver and `lunar-api` guard behind the admin
     * surface. A host that points `lunar.api.admin.guard` elsewhere can turn
     * the guard registration off.
     */
    protected function registerAdminGuard(): void
    {
        Auth::extend('lunar-api-key', function ($app, string $name, array $config): ApiKeyGuard {
            $guard = new ApiKeyGuard($app['request']);

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        if (! config('lunar.api.admin.register_guard', true)) {
            return;
        }

        $this->app['config']->set('auth.guards.lunar-api', [
            'driver' => 'lunar-api-key',
            'provider' => null,
        ]);
    }

    protected function registerRateLimiters(): void
    {
        RateLimiter::for('lunar-api-storefront', function (Request $request): Limit {
            return Limit::perMinute((int) config('lunar.api.storefront.throttle', 60))->by($request->ip());
        });

        RateLimiter::for('lunar-api-admin', function (Request $request): Limit {
            $token = $request->bearerToken();

            return Limit::perMinute((int) config('lunar.api.admin.throttle', 120))
                ->by($token ? hash('sha256', $token) : (string) $request->ip());
        });
    }

    /**
     * Render every exception raised under an API prefix as the JSON:API error
     * envelope, whatever the host's own exception handling does elsewhere.
     */
    protected function registerExceptionRenderer(): void
    {
        $this->callAfterResolving(ExceptionHandler::class, function ($handler): void {
            if (! method_exists($handler, 'renderable')) {
                return;
            }

            $handler->renderable(function (Throwable $e, Request $request) {
                if (! $this->isApiRequest($request)) {
                    return null;
                }

                return $this->app->make(ErrorRenderer::class)->render($e);
            });
        });
    }

    protected function isApiRequest(Request $request): bool
    {
        foreach (['storefront', 'admin'] as $surface) {
            if (! config("lunar.api.{$surface}.enabled", true)) {
                continue;
            }

            $prefix = trim((string) config("lunar.api.{$surface}.prefix"), '/');

            if ($prefix !== '' && ($request->is($prefix) || $request->is("{$prefix}/*"))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Each surface owns its middleware group, so a host appending middleware
     * to its own `api` group cannot double-stack or break the package. Both
     * run stateless: no session, no CSRF.
     */
    protected function registerMiddlewareGroups(): void
    {
        Route::middlewareGroup('lunar.api.storefront', [
            EnforceJson::class,
            ThrottleRequests::class.':lunar-api-storefront',
            SubstituteBindings::class,
            ResolveStorefrontContext::class,
            ResolveCart::class,
        ]);

        Route::middlewareGroup('lunar.api.admin', [
            EnforceJson::class,
            ThrottleRequests::class.':lunar-api-admin',
            SubstituteBindings::class,
            AuthenticateApiKey::class,
        ]);

        Route::aliasMiddleware('lunar.api.can', Authorize::class);
    }

    protected function registerRoutes(): void
    {
        $this->registerMiddlewareGroups();

        foreach (['storefront', 'admin'] as $surface) {
            if (! config("lunar.api.{$surface}.enabled", true) || ! config("lunar.api.{$surface}.register_routes", true)) {
                continue;
            }

            $this->registerSurfaceRoutes($this->api()->surface($surface, 'v1'));
        }
    }

    protected function registerSurfaceRoutes(SurfaceRegistry $registry): void
    {
        $surface = $registry->surface;
        $version = $registry->version;
        $prefix = trim((string) config("lunar.api.{$surface}.prefix"), '/')."/{$version}";

        Route::middleware(config("lunar.api.{$surface}.middleware", ["lunar.api.{$surface}"]))
            ->prefix($prefix)
            ->name("lunar.api.{$surface}.{$version}.")
            ->group(function () use ($registry, $surface, $version): void {
                Route::get('_schema', SchemaController::class)
                    ->name('schema')
                    ->defaults('surface', $surface)
                    ->defaults('version', $version);

                $this->loadRoutesFrom("{$this->root}/routes/{$surface}/{$version}.php");

                foreach ($registry->routeRegistrars() as $registrar) {
                    $registrar();
                }

                foreach ($registry->definitions() as $definition) {
                    foreach ($definition->extensionRoutes() as $routes) {
                        Route::prefix($definition->type())->name($definition->type().'.')->group($routes);
                    }
                }
            });
    }
}
