<?php

namespace Lunar\Search\Engines;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\EngineManager;
use Lunar\Models\Product;
use Lunar\Search\Data\SearchFacet;
use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;
use Typesense\Documents;
use Typesense\Exceptions\ServiceUnavailable;

class TypesenseEngine extends AbstractEngine
{
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

                unset($response['results'][0]);
                $otherResults = $response['results'];

                $facets = collect($completeResults['facet_counts'])->mapWithKeys(
                    fn ($facets) => [$facets['field_name'] => $facets]
                );

                foreach ($otherResults as $result) {
                    foreach ($result['facet_counts'] as $facet) {
                        $facets->put($facet['field_name'], $facet);
                    }
                }

                return [
                    ...$completeResults,
                    'facet_counts' => $facets->toArray(),
                ];
            });

        } catch (\GuzzleHttp\Exception\ConnectException|ServiceUnavailable  $e) {
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
            'highlights' => collect($hit['highlights'] ?? [])->map(
                fn ($highlight) => SearchHit\Highlight::from([
                    'field' => $highlight['field'],
                    'matches' => $highlight['matched_tokens'],
                    'snippet' => $highlight['snippet'],
                ])
            ),
            'document' => $hit['document'],
        ]));

        $facets = collect($results['facet_counts'] ?? [])->map(
            fn ($facet) => SearchFacet::from([
                'label' => $this->getFacetConfig($facet['field_name'])['label'] ?? '',
                'field' => $facet['field_name'],
                'values' => collect($facet['counts'])->map(
                    fn ($value) => SearchFacet\FacetValue::from([
                        'label' => $value['value'],
                        'value' => $value['value'],
                        'count' => $value['count'],
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

        return SearchResults::from([
            'query' => $this->query,
            'total_pages' => $paginator->lastPage(),
            'page' => $paginator->currentPage(),
            'count' => $paginator->total(),
            'per_page' => $paginator->perPage(),
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

            $filters = collect($options['filter_by']);

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

            if (! $this->query) {
                $queryBy = str_replace('embedding,', '', $queryBy);
            }

            $params = [
                ...$options,
                'query_by' => $queryBy,
                'q' => $searchQuery->query,
                'facet_query' => $facetQuery,
                'prefix' => false,
                'exlude_fields' => 'embedding',
                'max_facet_values' => 50,
                'sort_by' => $this->sortRaw ?: ($this->sortByIsValid() ? $this->sort : '_text_match:desc'),
                'facet_by' => implode(',', $searchQuery->facets),
            ];

            if ($this->query) {
                $params['vector_query'] = 'embedding:([], k: 200)';
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

    protected function getFieldConfig(): array
    {
        return config('scout.typesense.model-settings.'.$this->modelType.'.collection-schema.fields', []);
    }
}
