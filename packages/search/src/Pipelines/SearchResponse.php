<?php

namespace Lunar\Search\Pipelines;

use Lunar\Search\Data\SearchResults;

/**
 * Passable for the `lunar.search.pipelines.results` pipeline. Stages run
 * after `SearchResults` is built and may reorder, annotate or replace hits.
 */
final class SearchResponse
{
    public function __construct(
        public SearchRequest $request,
        public SearchResults $results,
    ) {}
}
