<?php

namespace Lunar\Search\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SearchResults extends Data
{
    public function __construct(
        public string $query,
        public int $count,
        public int $page,
        public int $perPage,
        public int $totalPages,
        #[DataCollectionOf(SearchHit::class)]
        public array $hits,
        #[DataCollectionOf(SearchFacet::class)]
        public array $facets = [],
    ) {}
}
