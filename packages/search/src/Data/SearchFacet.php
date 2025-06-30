<?php

namespace Lunar\Search\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/** @typescript */
class SearchFacet extends Data
{
    public function __construct(
        public string $label,
        public string $field,
        #[DataCollectionOf(SearchFacetValue::class)]
        public array $values,
        public bool $hierarchy = false
    ) {}
}
