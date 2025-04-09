<?php

namespace Lunar\Search\Data;

use Lunar\Search\Data\SearchFacet\FacetValue;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class SearchFacet extends Data
{
    public function __construct(
        public string $label,
        public string $field,
        #[DataCollectionOf(FacetValue::class)]
        public array $values,
        public bool $hierarchy = false
    ) {}
}
