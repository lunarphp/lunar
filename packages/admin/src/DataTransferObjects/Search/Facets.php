<?php

namespace GetCandy\Hub\DataTransferObjects\Search;

use Illuminate\Support\Collection;

class Facets
{
    public function __construct(
        public ?Collection $items = null
    ) {
        if (! $items) {
            $this->items = collect();
        }
    }

    public function get($field)
    {
        $facet = $this->items->first(
            fn ($facet) => $facet->field == $field
        );

        return $facet->values ?: collect();
    }
}
