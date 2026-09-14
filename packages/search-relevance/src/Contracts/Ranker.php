<?php

namespace Lunar\SearchRelevance\Contracts;

use Lunar\SearchRelevance\DataObjects\HitCollection;
use Lunar\SearchRelevance\DataObjects\RankingContext;

interface Ranker
{
    public function rank(RankingContext $context, HitCollection $hits): HitCollection;
}
