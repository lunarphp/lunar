<?php

namespace Lunar\Search\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/** @typescript */
class SearchHit extends Data
{
    public function __construct(
        #[DataCollectionOf(SearchHitHighlight::class)]
        public array $highlights,
        public array $document,
    ) {}
}
