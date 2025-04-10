<?php

namespace Lunar\Search\Data\SearchHit;

use Spatie\LaravelData\Data;

class Highlight extends Data
{
    public function __construct(
        public string $field,
        /** @var array<string> */
        public array $matches,
        public ?string $snippet,
    ) {}
}
