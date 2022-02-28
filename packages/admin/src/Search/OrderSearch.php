<?php

namespace GetCandy\Hub\Search;

use GetCandy\Hub\DataTransferObjects\Search\Facet;
use GetCandy\Hub\DataTransferObjects\Search\FacetField;
use GetCandy\Hub\DataTransferObjects\Search\Facets;
use GetCandy\Hub\DataTransferObjects\Search\SearchResults;
use GetCandy\Models\Order;

class OrderSearch extends AbstractSearch
{
    public function getModel()
    {
        return Order::class;
    }

    public function search($term, $options = [], $perPage = 25, $page = 1)
    {
        if ($this->driver == 'meilisearch') {
            return $this->meilisearch(
                $term,
                $options,
                $perPage,
                $page,
            );
        }
    }

    protected function meilisearch($term, $options = [], $perPage = 25, $page = 1)
    {
        $filters = $options['filters'] ?? [];

        $builder = Order::search($term, function ($engine, $query) use ($filters) {
            $parsedFilters = collect();

            $options = [
                // 'limit' => $this->perPage,
                // 'offset' => ($this->perPage * $this->page) - $this->perPage,
                'facetsDistribution' => (new Order)->getFilterableAttributes(),
                'sort' => ['placed_at:desc', 'created_at:desc'],
                // 'filter' => null,
                // 'sort' => [$this->sort],
            ];

            foreach ($filters as $field => $values) {
                if ($field == 'to' || ! $values) {
                    continue;
                }

                if ($field == 'from') {
                    $createdAtFilter = 'created_at >= '.now()->parse($values)->startOfDay()->timestamp;
                    $placedAtFilter = 'placed_at >= '.now()->parse($values)->startOfDay()->timestamp;

                    if (! empty($filters['to'])) {
                        $createdAtFilter .= ' AND created_at <= '.now()->parse($filters['to'])->endOfDay()->timestamp;
                        $placedAtFilter .= ' AND placed_at <= '.now()->parse($filters['to'])->endOfDay()->timestamp;
                    }

                    $dateFilter = "($createdAtFilter) OR ($placedAtFilter)";

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

            // dd($options);

            return $engine->search($query, $options);
        });

        // In order to get all the facets we need, we need to do a separate call to meilisearch with an empty
        // search and filters. Since Meilisearch will remove any facets not in the result which is the opposite of what
        // we want. Hopefully one day we can change this...
        $emptySearch = Order::search($term, function ($engine, $query) use ($options) {
            $options = [
                'facetsDistribution' => (new Order)->getFilterableAttributes(),
            ];

            return $engine->search(null, $options);
        })->raw();

        $results = $builder->paginate($perPage, null, $page);

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
