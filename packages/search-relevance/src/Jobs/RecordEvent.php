<?php

namespace Lunar\SearchRelevance\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;

/**
 * Writes one shopper event, silently dropping anything the search did not
 * show, anything arriving after the event window, and any repeat of an event
 * already recorded for the search, product and type.
 */
class RecordEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public const TYPES = ['click', 'basket', 'purchase'];

    public const SOURCES = ['organic', 'learned', 'explore'];

    public function __construct(
        public string $searchId,
        public int $productId,
        public int $position,
        public string $type,
        public string $source,
        public string $sessionId,
    ) {}

    /**
     * True when the search showed the product, the position is one it
     * displayed, and the search is recent enough to still accept events.
     */
    public static function accepts(SearchQuery $search, int $productId, int $position, int $windowMinutes): bool
    {
        $shown = array_map('intval', $search->shown ?? []);

        if ($windowMinutes > 0 && $search->created_at && $search->created_at->lt(now()->subMinutes($windowMinutes))) {
            return false;
        }

        return in_array($productId, $shown, true) && $position >= 1 && $position <= count($shown);
    }

    public function handle(Repository $config): void
    {
        if (! in_array($this->type, self::TYPES, true) || ! in_array($this->source, self::SOURCES, true)) {
            return;
        }

        $search = SearchQuery::query()->find($this->searchId);

        $window = (int) $config->get('lunar.search_relevance.guards.event_window_minutes', 120);

        if (! $search || ! self::accepts($search, $this->productId, $this->position, $window)) {
            return;
        }

        $exists = SearchEvent::query()
            ->where('search_id', $this->searchId)
            ->where('product_id', $this->productId)
            ->where('type', $this->type)
            ->exists();

        if ($exists) {
            return;
        }

        try {
            SearchEvent::query()->create([
                'search_id' => $this->searchId,
                'product_id' => $this->productId,
                'position' => $this->position,
                'type' => $this->type,
                'source' => $this->source,
                'session_id' => mb_substr($this->sessionId, 0, 64),
                'created_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // A concurrent worker recorded the same event first.
        }
    }
}
