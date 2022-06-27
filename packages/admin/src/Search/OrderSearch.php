<?php

namespace GetCandy\Hub\Search;

use GetCandy\Hub\DataTransferObjects\Search\Facet;
use GetCandy\Hub\DataTransferObjects\Search\FacetField;
use GetCandy\Hub\DataTransferObjects\Search\Facets;
use GetCandy\Hub\DataTransferObjects\Search\SearchResults;
use GetCandy\Models\Order;

class OrderSearch extends AbstractSearch
{
    /**
     * {@inheritDoc}
     */
    public function search($term, $options = [], $perPage = 25, $page = 1): SearchResults
    {
        if ($this->getDriverForModel(Order::class) == 'meilisearch') {
            return $this->meilisearch(
                $term,
                $options,
                $perPage,
                $page,
            );
        }

        $facets = new Facets;
        $results = Order::search($term)->paginate($perPage, 'page', $page);

        return new SearchResults(
            $results,
            $facets
        );
    }

    /**
     * Return meilisearch results.
     *
     * @param  string  $term
     * @param  array  $options
     * @param  int  $perPage
     * @param  int  $page
     * @return SearchResults
     */
    protected function meilisearch($term, $options = [], $perPage = 25, $page = 1)
    {
        $filters = $options['filters'] ?? [];

        $builder = Order::search($term, function ($engine, $query) use ($filters, $perPage, $page) {
            $parsedFilters = collect();

            $options = [
                'limit' => $perPage,
                'offset' => ($perPage * $page) - $perPage,
                'facetsDistribution' => (new Order)->getFilterableAttributes(),
                'sort' => ['placed_at:desc', 'created_at:desc'],
            ];

            foreach ($filters as $field => $values) {
                if (! $values) {
                    continue;
                }

                if ($field == 'to') {
                    $placedAtFilter = 'placed_at <= '.now()->parse($values)->endOfDay()->timestamp;
                    $dateFilter = "($placedAtFilter)";

                    $parsedFilters->push('('.$dateFilter.')');

                    continue;
                }

                if ($field == 'from') {
                    $placedAtFilter = 'placed_at >= '.now()->parse($values)->startOfDay()->timestamp;

                    if (! empty($filters['to'])) {
                        $placedAtFilter .= ' AND placed_at <= '.now()->parse($filters['to'])->endOfDay()->timestamp;
                    }

                    $dateFilter = "($placedAtFilter)";

                    $parsedFilters->push('('.$dateFilter.')');

                    continue;
                }

                if (empty($values)) {
                    continue;
                }

                $filterString = collect($values)->map(function ($value) use ($field) {
                    return $field.' = "'.$value.'"';
                })->join('OR');

                $parsedFilters->push('('.$filterString.')');
            }

            if ($parsedFilters->count()) {
                $options['filter'] = $parsedFilters->join(' AND ');
            }

            return $engine->search($query, $options);
        });

        // In order to get all the facets we need, we need to do a separate call to meilisearch with empty
        // search and filters. Since Meilisearch will remove any facets not in the result, which is the opposite of what
        // we want. Hopefully one day we can change this...
        $emptySearch = Order::search($term, function ($engine, $query) use ($options) {
            $options = [
                'facetsDistribution' => (new Order)->getFilterableAttributes(),
            ];

            return $engine->search(null, $options);
        })->raw();

        $results = tap($builder->paginate($perPage, 'page', $page), function ($orders) {
            return $orders->load(['billingAddress', 'currency']);
        });

        $facets = new Facets;

        foreach ($emptySearch['facetsDistribution'] as $facet => $values) {
            $fields = collect($values)->map(function ($count, $field) {
                return new FacetField($field, $count);
            })->values();

            $facets->items->push(
                new Facet($facet, $fields)
            );
        }

        return new SearchResults(
            $results,
            $facets
        );
    }
}
