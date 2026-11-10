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
     * With `trusted_sessions_only`, an event counts when its search had a known
     * customer, its own session is a cart, or it is a basket or purchase (only
     * ever recorded server-side, from a real cart line or order). A guest's
     * first search predates their cart, so their journey counts through it.
     *
     * @param  array<string, mixed>  $config
     */
    public function aggregate(string $version, array $config): int;
}
