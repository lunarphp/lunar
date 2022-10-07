<?php

namespace Lunar\Hub\DataTransferObjects\Search;

use Illuminate\Support\Collection;

class Facet
{
    public function __construct(
        public string $field,
        public Collection $values
    ) {
        // ...
    }
}
