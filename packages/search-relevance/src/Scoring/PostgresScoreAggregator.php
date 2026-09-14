<?php

namespace Lunar\SearchRelevance\Scoring;

class PostgresScoreAggregator extends SqlScoreAggregator
{
    protected function ageSecondsExpression(): string
    {
        return 'EXTRACT(EPOCH FROM now() - e.created_at)';
    }

    protected function minuteBucketExpression(): string
    {
        return "date_trunc('minute', created_at)";
    }

    protected function floatPlaceholder(): string
    {
        return '?::float';
    }
}
