<?php

namespace Lunar\Search\Engines;

use Illuminate\Support\Collection;
use Laravel\Scout\EngineManager;
use Lunar\Search\Data\SearchFacet;
use Lunar\Search\Data\SearchFacetValue;
use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;
use Meilisearch\Contracts\HybridSearchOptions;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Endpoints\Indexes;

class MeilisearchEngine extends AbstractEngine
{
    public function get(): SearchResults
    {
        $request = $this->pipeRequest();

        $paginator = $this->getRawResults(function (Indexes $indexes, string $query, array $options) {
            $engine = app(EngineManager::class)->engine('meilisearch');

            $queries = $this->buildSearch(
                $options,
                $indexes
            );

            $response = $engine->multiSearch($queries);

            $completeResults = $response['results'][0];

            unset($response['results'][0]);
            $otherResults = $response['results'];

            $facets = collect($completeResults['facetDistribution'] ?? []);

            foreach ($otherResults as $result) {
                foreach ($result['facetDistribution'] ?? [] as $field => $facet) {
                    $facets->put($field, $facet);
                }
            }

            return [
                ...$completeResults,
                'facetDistribution' => $facets,
            ];
        });

        $results = $paginator->items();

        [$sortField, $sortDirection] = $this->getSortParts();

        return $this->pipeResults($request, SearchResults::from([
            'query' => $results['query'],
            'totalPages' => $paginator->lastPage(),
            'page' => $paginator->currentPage(),
            'count' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'hits' => collect($results['hits'])->map(function ($hit) {
                $score = $hit['_rankingScore'] ?? null;
                unset($hit['_rankingScore']);

                return SearchHit::from([
                    'highlights' => collect(),
                    'document' => $hit,
                    'meta' => $score === null ? [] : ['score' => (float) $score],
                ]);
            }),
            'facets' => $this->mapFacets($results),
            'links' => (clone $paginator)->setCollection(
                collect($results['hits'])
            )->appends([
                'facets' => $this->facets,
            ])->links(),
        ]));
    }

    protected function buildSearch(array $options, Indexes $indexes): array
    {
        $searchQueries = $this->getSearchQueries();

        $requests = [];

        $facets = $this->getFacetConfig();

        foreach ($searchQueries as $searchQuery) {
            $filters = collect();

            if (config('scout.soft_delete', false)) {
                $filters->push('__soft_deleted = 0');
            }

            $msQuery = new SearchQuery;
            $msQuery->setIndexUid($indexes->getUid());
            $msQuery->setQuery($searchQuery->query);
            $msQuery->setFacets(array_keys($facets));
            $msQuery->setHitsPerPage($options['hitsPerPage']);
            $msQuery->setPage($options['page']);

            if ($this->sort) {
                $msQuery->setSort([$this->sort]);
            }

            foreach ($this->filters as $field => $values) {
                $filter = $this->mapFilter($field, $values);
                $filters->push($filter);
            }

            foreach ($searchQuery->facetFilters as $field => $values) {
                $filters->push($this->mapFilter($field, $values));
            }

            $msQuery->setFilter($filters->toArray());
            $msQuery->setShowRankingScore(true);

            $this->applyHybrid($msQuery, $searchQuery->query);
            $this->applyParams($msQuery);

            $requests[] = $msQuery;
        }

        return $requests;
    }

    /**
     * Hybrid retrieval when an embedder is configured. Only applies alongside
     * a search term; browse mode has nothing to embed.
     */
    protected function applyHybrid(SearchQuery $msQuery, string $query): void
    {
        $embedder = config('lunar.search.meilisearch.embedder');

        if (! $embedder || $query === '') {
            return;
        }

        $msQuery->setHybrid(
            (new HybridSearchOptions)
                ->setEmbedder($embedder)
                ->setSemanticRatio((float) config('lunar.search.meilisearch.semantic_ratio', 0.5))
        );

        if ($threshold = config('lunar.search.meilisearch.ranking_score_threshold')) {
            $msQuery->setRankingScoreThreshold((float) $threshold);
        }
    }

    /**
     * Map withParams() overrides onto the query object. Keys are Meilisearch
     * request parameter names (`attributesToSearchOn`, `matchingStrategy`,
     * `rankingScoreThreshold`, `hybrid`, ...) and resolve to the matching
     * setter. `hybrid` accepts an array with `embedder` and `semanticRatio`;
     * null removes the hybrid options.
     */
    protected function applyParams(SearchQuery $msQuery): void
    {
        foreach ($this->getParams() as $key => $value) {
            if ($key === 'hybrid') {
                $msQuery->setHybrid(
                    (new HybridSearchOptions)
                        ->setEmbedder($value['embedder'] ?? config('lunar.search.meilisearch.embedder', ''))
                        ->setSemanticRatio((float) ($value['semanticRatio'] ?? 0))
                );

                continue;
            }

            $setter = 'set'.ucfirst($key);

            if (method_exists($msQuery, $setter)) {
                $msQuery->{$setter}($value);
            }
        }
    }

    public function mapFacets(array $results): Collection
    {
        $facets = collect($results['facetDistribution'] ?? [])->map(
            fn ($values, $field) => SearchFacet::from([
                'label' => $this->getFacetConfig($field)['label'] ?? $field,
                'field' => $field,
                'values' => collect($values)->map(
                    fn ($count, $value) => SearchFacetValue::from([
                        'label' => $value,
                        'value' => $value,
                        'count' => $count,
                        'active' => in_array($value, $this->facets[$field] ?? []),
                    ])
                )->values(),
            ])
        )->values();

        foreach ($facets as $facet) {
            $facetConfig = $this->getFacetConfig($facet->field);
            foreach ($facet->values as $facetValue) {
                if (empty($facetConfig[$facetValue->value])) {
                    continue;
                }
                $facetValue->additional($facetConfig[$facetValue->value]);
            }
        }

        return $facets;
    }

    protected function mapFilter(string $field, mixed $value): string
    {
        $values = collect($value);

        if ($values->count() > 1) {
            $values = $values->map(
                fn ($value) => "{$field} = \"{$value}\""
            );

            return '('.$values->join(' OR ').')';
        }

        return $field.' = "'.$values->first().'"';
    }

    protected function getFieldConfig(): array
    {
        return [];
    }
}
