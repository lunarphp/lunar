<?php

namespace Lunar\Search\Engines;

use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;
use Typesense\Documents;

class DatabaseEngine extends AbstractEngine
{
    public function __construct()
    {
        $this->searchBuilder = function (Documents $documents, string $query, array $options) {
            return $documents->search([
                'q' => $query,
                ...$options,
                'facet_by' => 'colour,size',
                'per_page' => 2,
                //                'page' => ,
            ]);
        };
    }

    public function get(): mixed
    {
        $results = get_search_builder($this->modelType, $this->query, forceQuery: true)
            ->paginate();

        $documents = collect($results->items())->map(fn ($hit) => SearchHit::from([
            'highlights' => collect(),
            'document' => $hit->toSearchableArray(),
        ]));

        return SearchResults::from([
            'query' => $this->query,
            'total_pages' => $results->lastPage(),
            'page' => $results->currentPage(),
            'count' => $results->total(),
            'per_page' => $results->perPage(),
            'hits' => $documents,
            'facets' => collect(),
        ]);
    }
}
