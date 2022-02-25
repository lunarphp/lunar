<?php

namespace GetCandy\Hub\DataTransferObjects\Search;

class FacetField
{
    public function __construct(
        public string $value,
        public int $count
    ) {
        // ...
    }
}