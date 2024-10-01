<?php

namespace Lunar\Search\Data\SearchFacet;

use Spatie\LaravelData\Data;

class FacetValue extends Data
{
    public function __construct(
        public string $value,
        public int $count = 0,
    ) {}
}
