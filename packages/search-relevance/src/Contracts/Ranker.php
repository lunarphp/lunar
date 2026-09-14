<?php

namespace Lunar\SearchRelevance\Contracts;

use Lunar\SearchRelevance\Data\HitCollection;
use Lunar\SearchRelevance\Data\RankingContext;

interface Ranker
{
    public function rank(RankingContext $context, HitCollection $hits): HitCollection;
}
