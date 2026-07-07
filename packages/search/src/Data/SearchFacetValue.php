<?php

namespace Lunar\Search\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class SearchFacetValue extends Data
{
    public function __construct(
        public string $label,
        public string $value,
        public int $count = 0,
        public bool $active = false,
        #[DataCollectionOf(SearchFacetValue::class)]
        public array $children = []
    ) {}
}
