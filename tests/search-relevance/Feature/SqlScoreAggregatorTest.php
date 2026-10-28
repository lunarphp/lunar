<?php

use Lunar\SearchRelevance\Contracts\ScoreAggregator;
use Lunar\SearchRelevance\Scoring\MySqlScoreAggregator;
use Lunar\SearchRelevance\Scoring\PostgresScoreAggregator;
use Lunar\Tests\SearchRelevance\Support\ScoringFixture;
use Lunar\Tests\SearchRelevance\TestCase;

uses(TestCase::class)->group('search-relevance', 'cross-db');

$driver = env('DB_DRIVER', 'sqlite');

it('aggregates events into scores with the sql aggregator', function () use ($driver) {
    $aggregator = app(ScoreAggregator::class);

    expect($aggregator)->toBeInstanceOf($driver === 'pgsql' ? PostgresScoreAggregator::class : MySqlScoreAggregator::class);

    ScoringFixture::seed();

    ScoringFixture::assertScores($aggregator);
})->skip(! in_array($driver, ['mysql', 'pgsql'], true), 'Needs DB_DRIVER=mysql or pgsql');
