<?php

namespace Lunar\SearchRelevance\Learning;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lunar\SearchRelevance\Models\LearningOverride;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\Signals\QueryAffinitySignal;

/**
 * Staff overrides on what scoring may learn. Each change also drops the
 * affected learned rows and the score cache, so it applies immediately
 * rather than at the next nightly run.
 */
class Overrides
{
    public function __construct(protected QueryAffinitySignal $affinity) {}

    public function exclude(string $modelType, string $query, int $productId): void
    {
        LearningOverride::query()->firstOrCreate([
            'model_type' => $modelType,
            'normalised_query' => $query,
            'product_id' => $productId,
            'type' => LearningOverride::EXCLUDE,
        ], ['created_at' => now()]);

        SearchQueryScore::query()
            ->where('model_type', $modelType)
            ->where('normalised_query', $query)
            ->where('product_id', $productId)
            ->delete();

        $this->affinity->forget();
    }

    public function include(string $modelType, string $query, int $productId): void
    {
        LearningOverride::query()
            ->where('model_type', $modelType)
            ->where('normalised_query', $query)
            ->where('product_id', $productId)
            ->where('type', LearningOverride::EXCLUDE)
            ->delete();
    }

    /** Discard everything learned for the query; only events after now count again. */
    public function reset(string $modelType, string $query): void
    {
        LearningOverride::query()->create([
            'model_type' => $modelType,
            'normalised_query' => $query,
            'product_id' => null,
            'type' => LearningOverride::RESET,
            'created_at' => now(),
        ]);

        SearchQueryScore::query()
            ->where('model_type', $modelType)
            ->where('normalised_query', $query)
            ->delete();

        $this->affinity->forget();
    }

    /** @return Collection<int, int> */
    public function excluded(string $modelType, string $query): Collection
    {
        return LearningOverride::query()
            ->where('model_type', $modelType)
            ->where('normalised_query', $query)
            ->where('type', LearningOverride::EXCLUDE)
            ->orderBy('id')
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id);
    }

    public function resetAt(string $modelType, string $query): ?Carbon
    {
        $at = LearningOverride::query()
            ->where('model_type', $modelType)
            ->where('normalised_query', $query)
            ->where('type', LearningOverride::RESET)
            ->max('created_at');

        return $at ? Carbon::parse($at) : null;
    }
}
