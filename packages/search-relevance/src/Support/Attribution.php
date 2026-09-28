<?php

namespace Lunar\SearchRelevance\Support;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Session\Session;

/**
 * Remembers which search a product was clicked from so a later basket add or
 * purchase can be credited to it. Null-safe without a session (queue workers).
 *
 * Kept in the cache under the shopper's session id, not in the session
 * itself. The click arrives as a beacon while the navigation it starts, or
 * the basket add it precedes, is already in flight, and Laravel writes the
 * whole session payload at the end of every request: the request that read
 * the session before the beacon saved it would write it back without the
 * attribution. A cache key per product is written on its own.
 *
 * The id changes when login regenerates the session, so a click made before
 * signing in is not credited to a basket add after it.
 */
class Attribution
{
    public const KEY = 'lunar_search_relevance.attribution';

    public function __construct(
        protected Repository $config,
        protected Cache $cache,
        protected ?Session $session = null,
    ) {}

    public function remember(int $productId, string $searchId, int $position, string $source, string $sessionId): void
    {
        $key = $this->key($productId);

        if ($key === null) {
            return;
        }

        $ttl = (int) $this->config->get('lunar.search_relevance.attribution_ttl_minutes', 30);

        $this->cache->put($key, [
            'search_id' => $searchId,
            'position' => $position,
            'source' => $source,
            'session_id' => $sessionId,
            'expires' => now()->addMinutes($ttl)->getTimestamp(),
        ], now()->addMinutes($ttl));
    }

    /** @return array{search_id: string, position: int, source: string, session_id: string, expires: int}|null */
    public function find(int $productId): ?array
    {
        $key = $this->key($productId);
        $attribution = $key === null ? null : $this->cache->get($key);

        if (! is_array($attribution) || ! isset($attribution['search_id'])) {
            return null;
        }

        // The cache TTL covers this on stores that honour it; the timestamp
        // keeps expiry exact on those that round or ignore it.
        if (($attribution['expires'] ?? 0) < now()->getTimestamp()) {
            $this->forget($productId);

            return null;
        }

        return $attribution;
    }

    public function forget(int $productId): void
    {
        $key = $this->key($productId);

        if ($key !== null) {
            $this->cache->forget($key);
        }
    }

    protected function key(int $productId): ?string
    {
        $sessionId = $this->session?->getId();

        return $sessionId ? self::KEY.':'.$sessionId.':'.$productId : null;
    }
}
