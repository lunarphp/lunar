<?php

namespace Lunar\Search\Data;

use Illuminate\View\View;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

/** @typescript */
class SearchResults extends Data
{
    public function __construct(
        public ?string $query,
        public int $count,
        public int $page,
        public int $perPage,
        public int $totalPages,
        #[DataCollectionOf(SearchHit::class)]
        public array $hits,
        #[DataCollectionOf(SearchFacet::class)]
        public array $facets,
        // Serialised via toArray() to the paginator's link array, not the View.
        #[LiteralTypeScriptType('Array<{ url: string | null; label: string; active: boolean }>')]
        public View $links,
        public ?string $sortField = null,
        #[LiteralTypeScriptType("'asc' | 'desc' | null")]
        public ?string $sortDirection = null,
    ) {}

    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'links' => $this->links->getData()['paginator']->toArray()['links'],
        ];
    }
}
