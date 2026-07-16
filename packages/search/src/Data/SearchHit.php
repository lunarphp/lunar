<?php

namespace Lunar\Search\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class SearchHit extends Data
{
    public function __construct(
        #[DataCollectionOf(SearchHitHighlight::class)]
        public array $highlights,
        public array $document,
    ) {}
}
