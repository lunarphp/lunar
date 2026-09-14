<?php

namespace Lunar\SearchRelevance\Signals;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\ConnectionResolverInterface;
use Lunar\SearchRelevance\Contracts\Signal;
use Lunar\SearchRelevance\Data\RankingContext;

/** Learned per-query product scores from the nightly aggregation, current version only. */
class QueryAffinitySignal implements Signal
{
    public const CACHE_GENERATION_KEY = 'lunar.search_relevance.scores.generation';

    public function __construct(
        protected ConnectionResolverInterface $db,
        protected Cache $cache,
        protected Config $config,
    ) {}

    public function scores(RankingContext $context, array $productIds): array
    {
        return array_intersect_key($this->learned($context), array_flip($productIds));
    }

    /** @return array<int, float> every learned product for the query, product_id => relative, best first */
    public function learned(RankingContext $context): array
    {
        $key = implode(':', [
            'lunar.search_relevance.scores',
            $this->generation(),
            $context->version,
            md5($context->modelType.'|'.$context->normalisedQuery),
        ]);

        return $this->cache->remember($key, (int) $this->config->get('lunar.search_relevance.cache_ttl', 300), function () use ($context) {
            return $this->db->connection($this->config->get('lunar.database.connection'))
                ->table($this->config->get('lunar.database.table_prefix').'search_query_scores')
                ->where('model_type', $context->modelType)
                ->where('version', $context->version)
                ->where('normalised_query', $context->normalisedQuery)
                ->orderByDesc('relative')
                ->pluck('relative', 'product_id')
                ->map(fn ($relative) => (float) $relative)
                ->all();
        });
    }

    /** Bump the generation so every cached lookup misses without flushing the store. */
    public function forget(): void
    {
        $this->cache->forever(self::CACHE_GENERATION_KEY, $this->generation() + 1);
    }

    protected function generation(): int
    {
        return (int) $this->cache->get(self::CACHE_GENERATION_KEY, 0);
    }
}
