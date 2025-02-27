<?php

namespace Lunar\Search\Engines;

use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;

class DatabaseEngine extends AbstractEngine
{
    public function get(): mixed
    {
        $results = get_search_builder($this->modelType, $this->query)
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

    protected function getFieldConfig(): array
    {
        return [];
    }
}
