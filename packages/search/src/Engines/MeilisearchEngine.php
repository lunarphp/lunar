<?php

namespace Lunar\Search\Engines;

use Illuminate\Support\Collection;
use Laravel\Scout\EngineManager;
use Lunar\Search\Data\SearchFacet;
use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Endpoints\Indexes;

class MeilisearchEngine extends AbstractEngine
{
    public function get(): SearchResults
    {
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

        return SearchResults::from([
            'query' => $results['query'],
            'total_pages' => $paginator->lastPage(),
            'page' => $paginator->currentPage(),
            'count' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'hits' => collect($results['hits'])->map(fn ($hit) => SearchHit::from([
                'highlights' => collect(),
                'document' => $hit,
            ])),
            'facets' => $this->mapFacets($results),
            'links' => (clone $paginator)->setCollection(
                collect($results['hits'])
            )->appends([
                'facets' => http_build_query($this->facets),
            ])->links(),
        ]);
    }

    protected function buildSearch(array $options, Indexes $indexes): array
    {
        $searchQueries = $this->getSearchQueries();

        $requests = [];

        $facets = $this->getFacetConfig();

        foreach ($searchQueries as $searchQuery) {
            $filters = collect();

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
            $requests[] = $msQuery;
        }

        return $requests;
    }

    public function mapFacets(array $results): Collection
    {
        $facets = collect($results['facetDistribution'] ?? [])->map(
            fn ($values, $field) => SearchFacet::from([
                'label' => $this->getFacetConfig($field)['label'] ?? $field,
                'field' => $field,
                'values' => collect($values)->map(
                    fn ($count, $value) => SearchFacet\FacetValue::from([
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
