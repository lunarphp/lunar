<?php

namespace Lunar\SearchRelevance\Contracts;

interface ScoreAggregator
{
    /**
     * Upserts search_query_scores for the given version and returns rows written.
     *
     * `$config` is `lunar.search_relevance.scoring` plus the guards, and may
     * carry `keep_versions` (versions whose rows must survive the stale sweep).
     *
     * @param  array<string, mixed>  $config
     */
    public function aggregate(string $version, array $config): int;
}
