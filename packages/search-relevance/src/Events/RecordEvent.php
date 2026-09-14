<?php

namespace Lunar\SearchRelevance\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;

/** Writes one shopper event, silently dropping anything the search did not show. */
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

    /** True when the search showed the product and the position is one it displayed. */
    public static function accepts(SearchQuery $search, int $productId, int $position): bool
    {
        $shown = array_map('intval', $search->shown ?? []);

        return in_array($productId, $shown, true) && $position >= 1 && $position <= count($shown);
    }

    public function handle(): void
    {
        if (! in_array($this->type, self::TYPES, true) || ! in_array($this->source, self::SOURCES, true)) {
            return;
        }

        $search = SearchQuery::query()->find($this->searchId);

        if (! $search || ! self::accepts($search, $this->productId, $this->position)) {
            return;
        }

        SearchEvent::query()->create([
            'search_id' => $this->searchId,
            'product_id' => $this->productId,
            'position' => $this->position,
            'type' => $this->type,
            'source' => $this->source,
            'session_id' => mb_substr($this->sessionId, 0, 64),
            'created_at' => now(),
        ]);
    }
}
