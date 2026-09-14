<?php

namespace Lunar\SearchRelevance\Rankers;

use Illuminate\Contracts\Config\Repository;
use Lunar\SearchRelevance\Contracts\Ranker;
use Lunar\SearchRelevance\Data\Hit;
use Lunar\SearchRelevance\Data\HitCollection;
use Lunar\SearchRelevance\Data\RankingContext;
use Lunar\SearchRelevance\Signals\SignalCombiner;

/** Reorders only within engine-order buckets so a weak match can never leap above a strong one. */
class BucketedRanker implements Ranker
{
    protected int $bucketSize;

    public function __construct(
        protected SignalCombiner $signals,
        Repository $config,
    ) {
        $this->bucketSize = max(1, (int) $config->get('lunar.search_relevance.bucket_size', 10));
    }

    public function rank(RankingContext $context, HitCollection $hits): HitCollection
    {
        if (! $context->shouldRank() || $hits->isEmpty()) {
            return $hits;
        }

        $boosts = $this->signals->combine($context, $hits->productIds());

        $hits->each(fn (Hit $hit) => $hit->boost = $boosts[$hit->productId] ?? 0.0);

        $ranked = $hits
            ->chunk($this->bucketSize)
            ->flatMap(fn ($bucket) => $bucket->sortBy([
                fn (Hit $a, Hit $b) => $b->boost <=> $a->boost,
                fn (Hit $a, Hit $b) => $a->originalPosition <=> $b->originalPosition,
            ]))
            ->values();

        return new HitCollection($ranked->all());
    }
}
