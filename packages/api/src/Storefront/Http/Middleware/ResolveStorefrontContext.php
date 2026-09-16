<?php

namespace Lunar\Api\Storefront\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Lunar\Api\Http\Exceptions\ApiException;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Symfony\Component\HttpFoundation\Response;

/**
 * `X-Lunar-Channel`, `X-Lunar-Currency` and `Accept-Language` drive the
 * storefront session for the request; unknown channel or currency codes are
 * rejected, an unmatched language falls back to the default. The resolved
 * values are echoed on the response.
 */
class ResolveStorefrontContext
{
    public function __construct(
        protected StorefrontSession $storefront,
        protected Application $app,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($handle = $request->header('X-Lunar-Channel')) {
            $channel = Channel::query()->where('handle', $handle)->first()
                ?? throw ApiException::invalidHeader('X-Lunar-Channel', $handle);

            $this->storefront->setChannel($channel);
        }

        if ($code = $request->header('X-Lunar-Currency')) {
            $currency = Currency::query()->where('code', strtoupper((string) $code))->where('enabled', true)->first()
                ?? throw ApiException::invalidHeader('X-Lunar-Currency', $code);

            $this->storefront->setCurrency($currency);
        }

        $this->resolveLocale($request);

        $response = $next($request);

        $response->headers->set('X-Lunar-Channel', $this->storefront->getChannel()->handle);
        $response->headers->set('X-Lunar-Currency', $this->storefront->getCurrency()->code);
        $response->headers->set('Content-Language', $this->app->getLocale());

        return $response;
    }

    protected function resolveLocale(Request $request): void
    {
        $codes = Language::query()->orderByDesc('default')->pluck('code')->all();

        if ($codes === []) {
            return;
        }

        // Symfony returns the first candidate when nothing matches, so the
        // default language leads the list and doubles as the fallback.
        $this->app->setLocale($request->getPreferredLanguage($codes) ?? $codes[0]);
    }
}
