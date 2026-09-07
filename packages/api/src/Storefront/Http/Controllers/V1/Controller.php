<?php

namespace Lunar\Api\Storefront\Http\Controllers\V1;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Http\Controllers\ResourceController;
use Lunar\Api\Query\QueryApplier;
use Lunar\Api\Query\QueryParser;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Resources\Translations;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Contracts\StorefrontSession;

abstract class Controller extends ResourceController
{
    public function __construct(
        ApiManager $api,
        QueryParser $parser,
        QueryApplier $applier,
        protected Application $app,
    ) {
        parent::__construct($api, $parser, $applier);
    }

    /**
     * The storefront and cart sessions are request-scoped, and a route memoizes
     * its controller instance, so they are resolved per call rather than held.
     */
    protected function storefront(): StorefrontSession
    {
        return $this->app->make(StorefrontSession::class);
    }

    protected function cartSession(): CartSession
    {
        return $this->app->make(CartSession::class);
    }

    protected function registry(): SurfaceRegistry
    {
        return $this->api->storefront('v1');
    }

    protected function context(Request $request): SerializationContext
    {
        return new SerializationContext(
            registry: $this->registry(),
            translations: Translations::Resolved,
            locale: $this->app->getLocale(),
            storefront: $this->storefront()->context(),
            principal: $request->user(),
        );
    }

    protected function meta(Request $request, SerializationContext $context): array
    {
        return [
            'channel' => $this->storefront()->getChannel()->handle,
            'currency' => $this->storefront()->getCurrency()->code,
            'locale' => $context->locale(),
        ];
    }
}
