<?php

namespace GetCandy\Hub\DataTransferObjects\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchResults
{
    public function __construct(
        public LengthAwarePaginator $items,
        public Facets $facets
    ) {
        //
    }
}