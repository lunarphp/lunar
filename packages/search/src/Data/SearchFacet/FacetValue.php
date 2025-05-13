<?php

namespace Lunar\Search\Data\SearchFacet;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class FacetValue extends Data
{
    public function __construct(
        public string $label,
        public string $value,
        public int $count = 0,
        public bool $active = false,
        #[DataCollectionOf(FacetValue::class)]
        public array $children = []
    ) {}
}
