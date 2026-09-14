<?php

namespace Lunar\SearchRelevance\Rankers;

use Lunar\SearchRelevance\Contracts\Ranker;
use Lunar\SearchRelevance\DataObjects\HitCollection;
use Lunar\SearchRelevance\DataObjects\RankingContext;

class NullRanker implements Ranker
{
    public function rank(RankingContext $context, HitCollection $hits): HitCollection
    {
        return $hits;
    }
}
