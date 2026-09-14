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
        /** Engine score under `score` where available; pipeline stages add their own keys. */
        public array $meta = [],
    ) {}
}
