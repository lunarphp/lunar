<?php

namespace Lunar\Search\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class SearchHit extends Data
{
    public function __construct(
        #[DataCollectionOf(SearchHitHighlight::class)]
        public array $highlights,
        public array $document,
        /** Engine score under `score` where available; pipeline stages add their own keys. */
        #[LiteralTypeScriptType("{ score?: number; position?: number; original_position?: number; boost?: number | null; source?: 'organic' | 'learned' | 'explore'; [key: string]: unknown }")]
        public array $meta = [],
    ) {}
}
