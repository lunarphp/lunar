<?php

namespace Lunar\Search\Data;

use Lunar\Search\Data\SearchHit\Highlight;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class SearchHit extends Data
{
    public function __construct(
        #[DataCollectionOf(Highlight::class)]
        public array $highlights,
        public array $document,
    ) {}
}
