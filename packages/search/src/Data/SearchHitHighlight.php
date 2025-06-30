<?php

namespace Lunar\Search\Data;

use Spatie\LaravelData\Data;

/** @typescript */
class SearchHitHighlight extends Data
{
    public function __construct(
        public string $field,
        /** @var array<string> */
        public array $matches,
        public ?string $snippet,
    ) {}
}
