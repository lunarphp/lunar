<?php

namespace Lunar\Search\Engines;

use GuzzleHttp\Exception\ConnectException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\EngineManager;
use Lunar\Core\Models\Product;
use Lunar\Search\Data\SearchFacet;
use Lunar\Search\Data\SearchFacetValue;
use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchHitHighlight;
use Lunar\Search\Data\SearchResults;
use Typesense\Documents;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\ServiceUnavailable;

class TypesenseEngine extends AbstractEngine
{
    /**
     * Typesense defaults to 10 values per facet and caps the parameter at
     * 250; hierarchical category facets need far more than either default.
     */
    protected int $maxFacetValues = 50;

    public function maxFacetValues(int $count): self
    {
        $this->maxFacetValues = $count;

        return $this;
    }

    public function get(): SearchResults
    {
        try {
            $paginator = $this->getRawResults(function (Documents $documents, string $query, array $options) {
                $engine = app(EngineManager::class)->engine('typesense');

                $request = [
                    'searches' => $this->buildSearch(
                        $options
                    ),
                ];

                $response = $engine->getMultiSearch()->perform($request, [
                    'collection' => (new $this->modelType)->searchableAs(),
                ]);

                $completeResults = $response['results'][0];

                // A multi-search request resolves with HTTP 200 even when the
                // collection is missing; the failure surfaces as a per-search
                // error code in the body (e.g. 404 "Not found."). Treat any
                // non-200 result as an empty result set rather than letting the
                // missing 'hits'/'facet_counts' keys blow up downstream.
                if (($completeResults['code'] ?? 200) !== 200) {
                    Log::error('Typesense search failed: '.($completeResults['error'] ?? 'Unknown error'));

                    return [
                        'hits' => [],
                        'facet_counts' => [],
                    ];
                }

                unset($response['results'][0]);
                $otherResults = $response['results'];

                $facets = collect($completeResults['facet_counts'] ?? [])->mapWithKeys(
                    fn ($facets) => [$facets['field_name'] => $facets]
                );

                foreach ($otherResults as $result) {
                    foreach ($result['facet_counts'] ?? [] as $facet) {
                        $facets->put($facet['field_name'], $facet);
                    }
                }

                return [
                    ...$completeResults,
                    'facet_counts' => $facets->toArray(),
                ];
            });

        } catch (ConnectException|ServiceUnavailable|ObjectNotFound $e) {
            Log::error($e->getMessage());
            $paginator = new LengthAwarePaginator(
                items: [
                    'hits' => [],
                    'facet_counts' => [],
                ],
                total: 0,
                perPage: $this->perPage,
                currentPage: 1,
            );
        }

        $results = $paginator->items();

        $documents = collect($results['hits'])->map(fn ($hit) => SearchHit::from([
            'highlights' => collect($hit['highlights'] ?? [])->flatMap(function ($highlight) {
                // Matches on string[] fields are highlighted per element:
                // a `snippets` list with index-aligned nested `matched_tokens`.
                // Scalar fields return a single `snippet` with a flat token
                // list.
                if (isset($highlight['snippets'])) {
                    return collect($highlight['snippets'])->map(
                        fn ($snippet, $index) => SearchHitHighlight::from([
                            'field' => $highlight['field'],
                            'matches' => $highlight['matched_tokens'][$index] ?? [],
                            'snippet' => $snippet,
                        ])
                    )->values();
                }

                return [SearchHitHighlight::from([
                    'field' => $highlight['field'],
                    'matches' => $highlight['matched_tokens'] ?? [],
                    'snippet' => $highlight['snippet'] ?? null,
                ])];
            }),
            'document' => $hit['document'],
        ]));

        // The raw facet_counts are keyed by field name (the multi-search merge
        // above needs that for dedupe); reset to a list so `facets` serialises
        // as a JSON array, matching MeilisearchEngine::mapFacets().
        $facets = collect($results['facet_counts'] ?? [])->values()->map(
            fn ($facet) => SearchFacet::from([
                'label' => $this->getFacetConfig($facet['field_name'])['label'] ?? '',
                'field' => $facet['field_name'],
                'values' => collect($facet['counts'])->map(
                    fn ($value) => SearchFacetValue::from([
                        'label' => $value['value'],
                        'value' => $value['value'],
                        'count' => $value['count'],
                        'active' => in_array($value['value'], $this->facets[$facet['field_name']] ?? []),
                    ])
                ),
            ])
        );

        foreach ($facets as $facet) {
            $facetConfig = $this->getFacetConfig($facet->field);

            foreach ($facet->values as $facetValue) {
                $valueConfig = $facetConfig['values'][$facetValue->value] ?? null;

                if (! $valueConfig) {
                    continue;
                }

                $facetValue->label = $valueConfig['label'] ?? $facetValue->value;
                unset($valueConfig['label']);

                $facetValue->additional($valueConfig);
            }
        }

        $newPaginator = clone $paginator;

        [$sortField, $sortDirection] = $this->getSortParts();

        return SearchResults::from([
            'query' => $this->query,
            'totalPages' => $paginator->lastPage(),
            'page' => $paginator->currentPage(),
            'count' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'hits' => $documents,
            'facets' => $facets,
            'links' => $newPaginator->setCollection(
                collect($results['hits'])
            )->appends([
                'facets' => http_build_query($this->facets),
            ])->links(),
        ]);
    }

    protected function buildSearch(array $options): array
    {
        $searchQueries = $this->getSearchQueries();

        $requests = [];

        $facets = $this->getFacetConfig();

        foreach ($searchQueries as $searchQuery) {

            // Scout passes filter_by through even when empty; a blank entry
            // joined with real filters produces "unbalanced `&&` operands".
            $filters = collect($options['filter_by'])->filter(fn ($filter) => filled($filter));

            foreach ($this->filters as $key => $value) {
                $filters->push($key.':'.collect($value)->join(','));
            }

            $facetQuery = collect();

            $facetConfig = collect($facets)->filter(
                fn ($facet, $field) => in_array($field, $searchQuery->facets)
            );

            foreach ($facetConfig as $facetConfigValue) {
                if (empty($facetConfigValue['facet_query'])) {
                    continue;
                }
                $facetQuery->push($facetConfigValue['facet_query']);
            }

            $facetQuery = $facetQuery->join(',');

            foreach ($searchQuery->facetFilters as $field => $values) {
                $values = collect($values)->map(function ($value) {
                    if ($value == 'false' || $value == 'true') {
                        return $value;
                    }

                    return '`'.$value.'`';
                });

                if ($values->count() > 1) {
                    $filters->push($field.':['.collect($values)->join(',').']');

                    continue;
                }

                $filters->push($field.':='.collect($values)->join(','));
            }

            $queryBy = $options['query_by'];
            $queryByWeights = $options['query_by_weights'] ?? null;
            $infix = $options['infix'] ?? null;
            $prefix = $options['prefix'] ?? false;

            // Without a search term there is nothing to embed, so drop the
            // embedding field from query_by — together with its entries in the
            // position-aligned weight/infix/prefix lists, otherwise Typesense
            // rejects the whole request over the count mismatch.
            if (! $this->query) {
                $fields = array_map('trim', explode(',', $queryBy));
                $embeddingIndex = array_search('embedding', $fields, true);

                if ($embeddingIndex !== false) {
                    unset($fields[$embeddingIndex]);
                    $queryBy = implode(',', $fields);
                    $queryByWeights = $this->stripListEntry($queryByWeights, $embeddingIndex);
                    $infix = $this->stripListEntry($infix, $embeddingIndex);
                    $prefix = $this->stripListEntry($prefix, $embeddingIndex);
                }
            }

            $params = [
                ...$options,
                'query_by' => $queryBy,
                // Typesense requires q; '*' is its match-all for browse mode.
                'q' => $searchQuery->query ?: '*',
                'facet_query' => $facetQuery,
                'prefix' => $prefix,
                // The embedding vector is never wanted in payloads; hosts can
                // exclude further fields via search-parameters.
                'exclude_fields' => collect(explode(',', (string) ($options['exclude_fields'] ?? '')))
                    ->map(fn (string $field) => trim($field))
                    ->filter()
                    ->push('embedding')
                    ->unique()
                    ->join(','),
                'max_facet_values' => $this->maxFacetValues,
                'sort_by' => $this->sortRaw ?: ($this->sortByIsValid() ? $this->sort : '_text_match:desc'),
                'facet_by' => implode(',', $searchQuery->facets),
            ];

            if ($queryByWeights !== null) {
                $params['query_by_weights'] = $queryByWeights;
            }

            if ($infix !== null) {
                $params['infix'] = $infix;
            }

            // Hybrid semantic search only works when the collection schema
            // actually declares an auto-embed `embedding` field; requesting a
            // vector query without one 404s the whole multi-search. Hosts can
            // pin k/alpha with a `vector_query` search parameter; it only
            // applies alongside a search term, so browse mode drops it.
            if ($this->query && $this->schemaHasEmbeddingField()) {
                $params['vector_query'] ??= 'embedding:([], k: 200)';
            } else {
                unset($params['vector_query']);
            }

            if ($filters->count()) {
                $params['filter_by'] = $filters->join(' && ');
            }

            $requests[] = $params;
        }

        return $requests;
    }

    public function deleteByIds(Collection $ids): array
    {
        $typesense = app(EngineManager::class)->engine('typesense');
        $index = (new Product)->searchableAs();

        return $typesense->getCollections()[$index]->documents->delete([
            'filter_by' => 'id: ['.$ids->join(',').']',
        ]);
    }

    /**
     * Remove one entry from a comma-separated, position-aligned parameter
     * list (query_by_weights, infix). Returns the list untouched when it is
     * not a string.
     */
    protected function stripListEntry(mixed $list, int $index): mixed
    {
        if (! is_string($list)) {
            return $list;
        }

        $values = array_map('trim', explode(',', $list));
        unset($values[$index]);

        return implode(',', $values);
    }

    protected function schemaHasEmbeddingField(): bool
    {
        return collect($this->getFieldConfig())
            ->contains(fn (array $field): bool => ($field['name'] ?? null) === 'embedding');
    }

    protected function getFieldConfig(): array
    {
        return config('scout.typesense.model-settings.'.$this->modelType.'.collection-schema.fields', []);
    }
}
