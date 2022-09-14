<?php

namespace Lunar\Hub\DataTransferObjects\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchResults
{
    public function __construct(
        public LengthAwarePaginator $items,
        public Facets $facets
    ) {
        //
    }
}
