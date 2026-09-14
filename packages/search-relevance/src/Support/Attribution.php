<?php

namespace Lunar\SearchRelevance\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Session\Session;

/**
 * Remembers which search a product was clicked from so a later basket add or
 * purchase can be credited to it. Null-safe without a session (queue workers).
 */
class Attribution
{
    public const KEY = 'lunar_search_relevance.attribution';

    public function __construct(
        protected Repository $config,
        protected ?Session $session = null,
    ) {}

    public function remember(int $productId, string $searchId, int $position, string $source, string $sessionId): void
    {
        $ttl = (int) $this->config->get('lunar.search_relevance.attribution_ttl_minutes', 30);

        $this->session?->put(self::KEY.'.'.$productId, [
            'search_id' => $searchId,
            'position' => $position,
            'source' => $source,
            'session_id' => $sessionId,
            'expires' => now()->addMinutes($ttl)->getTimestamp(),
        ]);
    }

    /** @return array{search_id: string, position: int, source: string, session_id: string, expires: int}|null */
    public function find(int $productId): ?array
    {
        $attribution = $this->session?->get(self::KEY.'.'.$productId);

        if (! is_array($attribution) || ! isset($attribution['search_id'])) {
            return null;
        }

        if (($attribution['expires'] ?? 0) < now()->getTimestamp()) {
            $this->forget($productId);

            return null;
        }

        return $attribution;
    }

    public function forget(int $productId): void
    {
        $this->session?->forget(self::KEY.'.'.$productId);
    }
}
