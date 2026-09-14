<?php

namespace Lunar\SearchRelevance\Rankers;

use Lunar\SearchRelevance\Contracts\Ranker;
use Lunar\SearchRelevance\Data\HitCollection;
use Lunar\SearchRelevance\Data\RankingContext;

class NullRanker implements Ranker
{
    public function rank(RankingContext $context, HitCollection $hits): HitCollection
    {
        return $hits;
    }
}
