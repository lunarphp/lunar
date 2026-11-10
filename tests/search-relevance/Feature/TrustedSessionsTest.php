<?php

use Lunar\SearchRelevance\Contracts\ScoreAggregator;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\Tests\SearchRelevance\Support\ScoringFixture;
use Lunar\Tests\SearchRelevance\TestCase;

uses(TestCase::class)->group('search-relevance', 'cross-db');

/**
 * Runs against the aggregator bound for the database: the PHP one on SQLite,
 * the SQL ones on MySQL and PostgreSQL, so both apply the same trust rule.
 */
function trustedScores(): array
{
    app(ScoreAggregator::class)->aggregate(ScoringFixture::VERSION, [
        ...ScoringFixture::config(),
        'min_sessions' => 3,
        'trusted_sessions_only' => true,
    ]);

    return SearchQueryScore::query()->where('normalised_query', 'guest')->pluck('score', 'product_id')->map(fn ($score) => (float) $score)->all();
}

it('counts a guest journey that reaches the basket and purchase', function () {
    // A first-time guest searches before any cart exists, so the search and its
    // click carry a session id; the basket and purchase are recorded server-side
    // from a real cart line and order.
    foreach (['session:a', 'session:b', 'session:c'] as $session) {
        $search = ScoringFixture::search('guest', $session, [1, 2]);
        ScoringFixture::event($search, 1, 1, 'click');
        ScoringFixture::event($search, 1, 1, 'basket');
        ScoringFixture::event($search, 1, 1, 'purchase');
        ScoringFixture::event($search, 2, 2, 'click');
    }

    $scores = trustedScores();

    expect($scores)->toHaveKey(1)
        ->and($scores)->not->toHaveKey(2);
});
