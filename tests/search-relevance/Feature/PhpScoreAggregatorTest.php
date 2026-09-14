<?php

use Illuminate\Support\Facades\Config;
use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Contracts\ScoreAggregator;
use Lunar\SearchRelevance\Data\RankingContext;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\Scoring\PhpScoreAggregator;
use Lunar\SearchRelevance\Signals\QueryAffinitySignal;
use Lunar\Tests\SearchRelevance\Support\ScoringFixture;
use Lunar\Tests\SearchRelevance\TestCase;

uses(TestCase::class)->group('search-relevance');

it('is the bound aggregator on sqlite', function () {
    expect(app(ScoreAggregator::class))->toBeInstanceOf(PhpScoreAggregator::class);
});

it('aggregates events into scores', function () {
    ScoringFixture::seed();

    ScoringFixture::assertScores(app(PhpScoreAggregator::class));
});

it('keeps only the top products per query', function () {
    ScoringFixture::seed();

    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, [...ScoringFixture::config(), 'max_products_per_query' => 2]);

    expect(SearchQueryScore::query()->where('normalised_query', 'w')->orderByDesc('score')->pluck('product_id')->all())->toBe([2, 3])
        ->and(SearchQueryScore::query()->where('normalised_query', 'p')->orderByDesc('score')->pluck('product_id')->all())->toBe([3, 2]);
});

it('invalidates the learned-score cache', function () {
    Config::set('lunar.search_relevance.cache_ttl', 3600);
    $context = new RankingContext(Product::class, 'w', 'session:x', null, 'on', null, md5('[]'), ScoringFixture::VERSION);
    $signal = app(QueryAffinitySignal::class);

    expect($signal->learned($context))->toBe([]);

    ScoringFixture::seed();
    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, ScoringFixture::config());

    expect(array_keys($signal->learned($context)))->toBe([2, 3, 1])
        ->and($signal->scores($context, [1, 2]))->toMatchArray([1 => 0.2, 2 => 1.0]);
});
