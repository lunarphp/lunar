<?php

namespace Lunar\Search\Engines;

use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;

class DatabaseEngine extends AbstractEngine
{
    public function get(): mixed
    {
        // Scout's builder, not the admin package's get_search_builder() helper —
        // this package must work without lunar/admin installed. Eager-load the
        // relations the indexer touches so mapping hits below doesn't lazy-load
        // per row.
        $results = $this->modelType::search($this->query)
            ->query(fn ($query) => (new $this->modelType)->indexer()->makeAllSearchableUsing($query))
            ->paginate($this->perPage);

        $documents = collect($results->items())->map(fn ($hit) => SearchHit::from([
            'highlights' => collect(),
            'document' => $hit->toSearchableArray(),
        ]));

        return SearchResults::from([
            'query' => $this->query,
            'totalPages' => $results->lastPage(),
            'page' => $results->currentPage(),
            'count' => $results->total(),
            'perPage' => $results->perPage(),
            'hits' => $documents,
            'facets' => collect(),
            'links' => $results->links(),
        ]);
    }

    protected function getFieldConfig(): array
    {
        return [];
    }
}
