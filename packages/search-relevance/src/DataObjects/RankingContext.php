<?php

namespace Lunar\SearchRelevance\DataObjects;

final class RankingContext
{
    public function __construct(
        public readonly string $modelType,
        public readonly string $normalisedQuery,
        public readonly string $sessionId,
        public readonly ?int $customerId,
        public readonly string $mode,
        public readonly ?string $sort,
        public readonly string $filtersHash,
        public readonly string $version,
    ) {}

    public function shouldRank(): bool
    {
        return $this->mode !== 'off'
            && ! $this->sort
            && $this->normalisedQuery !== ''
            && $this->normalisedQuery !== '*';
    }
}
