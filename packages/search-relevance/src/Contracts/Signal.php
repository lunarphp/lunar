<?php

namespace Lunar\SearchRelevance\Contracts;

use Lunar\SearchRelevance\Data\RankingContext;

interface Signal
{
    /**
     * @param  array<int, int>  $productIds
     * @return array<int, float> product_id => 0..1
     */
    public function scores(RankingContext $context, array $productIds): array;
}
