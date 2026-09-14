<?php

namespace Lunar\SearchRelevance\Scoring;

class MySqlScoreAggregator extends SqlScoreAggregator
{
    protected function ageSecondsExpression(): string
    {
        return 'TIMESTAMPDIFF(SECOND, e.created_at, NOW())';
    }

    protected function minuteBucketExpression(): string
    {
        return "DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')";
    }

    protected function floatPlaceholder(): string
    {
        return '?';
    }
}
