<?php

namespace Lunar\SearchRelevance\Scoring;

final class ReplayResult
{
    public function __construct(
        public readonly int $searches,
        public readonly float $mrrShown,
        public readonly float $mrrRanked,
        public readonly float $improvedShare,
        public readonly float $worsenedShare,
    ) {}
}
