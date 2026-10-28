<?php

namespace Lunar\SearchRelevance\Pipelines;

use Closure;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;
use Lunar\Search\Engines\DatabaseEngine;
use Lunar\Search\Pipelines\SearchResponse;
use Lunar\SearchRelevance\Contracts\Ranker;
use Lunar\SearchRelevance\DataObjects\Hit;
use Lunar\SearchRelevance\DataObjects\HitCollection;
use Lunar\SearchRelevance\DataObjects\RankingContext;
use Lunar\SearchRelevance\Logging\SearchLogger;
use Lunar\SearchRelevance\Signals\QueryAffinitySignal;
use Throwable;

/**
 * Reranks the widened window, slices it back to the requested page, stamps
 * the tracking metadata and logs the search. A request that was not widened
 * is still stamped and logged so clicks on it can be attributed.
 */
class RankResults
{
    public function __construct(
        protected Ranker $ranker,
        protected QueryAffinitySignal $affinity,
        protected SearchLogger $logger,
        protected Cache $cache,
        protected Config $config,
    ) {}

    public function handle(SearchResponse $response, Closure $next): SearchResponse
    {
        $context = $response->request->context['relevance'] ?? null;

        if (! $context instanceof RankingContext) {
            return $next($response);
        }

        $searchId = (string) Str::ulid();

        if (empty($response->request->context['relevance_widened'])) {
            $this->stampPassthrough($response, $context, $searchId);

            return $next($response);
        }

        $request = $response->request;
        $results = $response->results;

        $original = $this->toHits($results->hits);
        $ranked = $this->rankedWindow($response, $context, $original);

        $displayed = $context->mode === 'on' ? $ranked : $original;
        // The engine's total, not the window's: a search matching more than
        // the window keeps its later pages, which the engine serves unwidened.
        // Learned products unioned into the window add to it.
        $count = $results->count + max(0, $displayed->count() - $original->count());
        $perPage = max(1, $request->requestedPerPage);
        $page = max(1, $request->requestedPage);
        $offset = ($page - 1) * $perPage;

        $hitsById = collect($results->hits)->keyBy(fn (SearchHit $hit) => (int) ($hit->document['id'] ?? 0));

        $pageHits = [];
        foreach ($displayed->slice($offset, $perPage)->values() as $index => $hit) {
            $pageHits[] = $this->toSearchHit($hit, $hitsById->get($hit->productId), $offset + $index + 1);
        }

        $paginator = new LengthAwarePaginator($pageHits, $count, $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $response->results = SearchResults::from([
            'query' => $results->query,
            'count' => $count,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => max(1, (int) ceil($count / $perPage)),
            'hits' => $pageHits,
            'facets' => $results->facets,
            'links' => $paginator->links(),
            'sortField' => $results->sortField,
            'sortDirection' => $results->sortDirection,
            'meta' => [
                ...$results->meta,
                'search_id' => $searchId,
                'ranking_mode' => $context->mode,
                'ranking_version' => $context->version,
            ],
        ]);

        $logged = (int) $this->config->get('lunar.search_relevance.impressions_logged', 50);
        $rankedIds = array_slice($ranked->productIds(), 0, $logged);
        $shownIds = array_slice($displayed->productIds(), 0, $logged);

        $this->logger->log(
            $searchId,
            $context,
            (string) $request->engine->getQuery(),
            $count,
            $shownIds,
            $rankedIds === $shownIds ? null : $rankedIds,
            $this->features($ranked, $logged),
        );

        return $next($response);
    }

    /** Build (or read from cache) the learned-union plus ranked window. */
    protected function rankedWindow(SearchResponse $response, RankingContext $context, HitCollection $original): HitCollection
    {
        $key = 'lunar.search_relevance.window:'.md5(implode('|', [
            $context->modelType,
            $context->version,
            $context->mode,
            $context->normalisedQuery,
            $context->filtersHash,
            (string) $context->customerId,
        ]));

        $cached = $this->cache->get($key);

        if (is_array($cached)) {
            return HitCollection::fromArrays($cached);
        }

        $window = $this->unionLearned($response, $context, new HitCollection($original->all()));
        $ranked = $this->ranker->rank($context, $window);

        $this->cache->put($key, $ranked->toArrays(), (int) $this->config->get('lunar.search_relevance.cache_ttl', 300));

        return $ranked;
    }

    /**
     * Learned products the engine did not return are inserted at the head of
     * the second bucket: they can win a first-page slot but never leap above
     * the strongest engine matches.
     */
    protected function unionLearned(SearchResponse $response, RankingContext $context, HitCollection $hits): HitCollection
    {
        $union = $this->config->get('lunar.search_relevance.learned_union', []);
        $max = (int) ($union['max'] ?? 5);
        $minRelative = (float) ($union['min_relative'] ?? 0.1);

        if ($max < 1) {
            return $hits;
        }

        $learned = array_filter($this->affinity->learned($context), fn (float $relative) => $relative >= $minRelative);
        $missing = array_slice(array_values(array_diff(array_keys($learned), $hits->productIds())), 0, $max);

        if ($missing === []) {
            return $hits;
        }

        $documents = $this->fetchDocuments($response, $context, $missing);

        if ($documents === []) {
            return $hits;
        }

        $insertAt = min((int) $this->config->get('lunar.search_relevance.bucket_size', 10), $hits->count());
        $extra = array_map(fn (array $document) => new Hit((int) $document['id'], 0, 0.0, $document, 'learned'), $documents);

        $merged = collect($hits->slice(0, $insertAt)->all())
            ->concat($extra)
            ->concat($hits->slice($insertAt)->all())
            ->values()
            ->map(fn (Hit $hit, int $index) => $hit->withPosition($index + 1));

        return new HitCollection($merged->all());
    }

    /**
     * Fetch documents by id through a copy of the engine that served the
     * request, keeping only the ids asked for and the learned order. The
     * Database engine ignores filters, so its documents come from the model.
     * Any failure just skips the union.
     *
     * @param  array<int, int>  $ids
     * @return array<int, array<string, mixed>>
     */
    protected function fetchDocuments(SearchResponse $response, RankingContext $context, array $ids): array
    {
        try {
            $documents = $response->request->engine instanceof DatabaseEngine
                ? $this->documentsFromModel($context->modelType, $ids)
                : $this->documentsFromEngine($response, $ids);
        } catch (Throwable) {
            return [];
        }

        return array_values(array_filter(array_map(fn (int $id) => $documents[$id] ?? null, $ids)));
    }

    /** @return array<int, array<string, mixed>> keyed by product id */
    protected function documentsFromEngine(SearchResponse $response, array $ids): array
    {
        $engine = clone $response->request->engine;
        $results = $engine->query('')->filter(['id' => $ids])->page(1)->perPage(count($ids))->get();

        if (! $results instanceof SearchResults) {
            return [];
        }

        $wanted = array_flip($ids);
        $documents = [];

        foreach ($results->hits as $hit) {
            $id = (int) ($hit->document['id'] ?? 0);

            if (isset($wanted[$id]) && ! isset($documents[$id])) {
                $documents[$id] = $hit->document;
            }
        }

        return $documents;
    }

    /** @return array<int, array<string, mixed>> keyed by product id */
    protected function documentsFromModel(string $modelType, array $ids): array
    {
        /** @var Model $prototype */
        $prototype = new $modelType;
        $query = $prototype->newQuery()->whereKey($ids);

        if (method_exists($prototype, 'indexer')) {
            $query = $prototype->indexer()->makeAllSearchableUsing($query);
        }

        return $query->get()
            ->mapWithKeys(fn (Model $model) => [(int) $model->getKey() => $model->toSearchableArray()])
            ->all();
    }

    /** Stamp a search that ran at its requested size so it can still be tracked. */
    protected function stampPassthrough(SearchResponse $response, RankingContext $context, string $searchId): void
    {
        $results = $response->results;
        $offset = ($results->page - 1) * $results->perPage;
        $shown = [];

        foreach ($results->hits as $index => $hit) {
            $position = $offset + $index + 1;
            $hit->meta = [...$hit->meta, 'position' => $position, 'original_position' => $position, 'source' => 'organic'];
            $shown[] = (int) ($hit->document['id'] ?? 0);
        }

        $results->meta = [
            ...$results->meta,
            'search_id' => $searchId,
            'ranking_mode' => $context->mode,
            'ranking_version' => $context->version,
        ];

        $this->logger->log(
            $searchId,
            $context,
            (string) $response->request->engine->getQuery(),
            $results->count,
            array_slice($shown, 0, (int) $this->config->get('lunar.search_relevance.impressions_logged', 50)),
            null,
        );
    }

    /** @param array<int, SearchHit> $hits */
    protected function toHits(array $hits): HitCollection
    {
        $collection = new HitCollection;

        foreach (array_values($hits) as $index => $hit) {
            $collection->push(new Hit(
                productId: (int) ($hit->document['id'] ?? 0),
                originalPosition: $index + 1,
                score: (float) ($hit->meta['score'] ?? 0),
                document: $hit->document,
            ));
        }

        return $collection;
    }

    protected function toSearchHit(Hit $hit, ?SearchHit $engineHit, int $position): SearchHit
    {
        return SearchHit::from([
            'highlights' => $engineHit?->highlights ?? [],
            'document' => $hit->document,
            'meta' => [
                ...($engineHit?->meta ?? []),
                'position' => $position,
                'original_position' => $hit->originalPosition,
                'boost' => $hit->boost,
                'source' => $hit->source,
            ],
        ]);
    }

    /**
     * Request-time features per logged impression, the raw material for a
     * future learning-to-rank model.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function features(HitCollection $ranked, int $limit): array
    {
        return $ranked->take($limit)->values()->map(fn (Hit $hit, int $index) => [
            'id' => $hit->productId,
            'pos' => $index + 1,
            'orig' => $hit->originalPosition,
            'score' => $hit->score,
            'boost' => $hit->boost,
            'src' => $hit->source,
        ])->all();
    }
}
