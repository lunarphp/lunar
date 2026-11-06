<?php

use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Learning\Overrides;
use Lunar\SearchRelevance\Models\LearningOverride;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\Scoring\PhpScoreAggregator;
use Lunar\Tests\SearchRelevance\Support\ScoringFixture;
use Lunar\Tests\SearchRelevance\TestCase;

uses(TestCase::class)->group('search-relevance');

function guardConfig(array $overrides = []): array
{
    return [...ScoringFixture::config(), 'min_sessions' => 1, ...$overrides];
}

function scoresFor(string $query): array
{
    return SearchQueryScore::query()->where('normalised_query', $query)->orderBy('product_id')->pluck('score', 'product_id')->map(fn ($s) => round((float) $s, 2))->all();
}

it('counts one event of each type per session, product and query', function () {
    // One session replaying the same click across ten searches is worth one click.
    for ($i = 0; $i < 10; $i++) {
        ScoringFixture::event(ScoringFixture::search('dup', 'cart:1', [1, 2]), 1, 1, 'click');
    }
    ScoringFixture::event(ScoringFixture::search('dup', 'cart:2', [1, 2]), 2, 1, 'click');

    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, guardConfig());

    expect(scoresFor('dup'))->toBe([1 => 1.0, 2 => 1.0]);
});

it('ignores sessions without a cart or customer when trusted_sessions_only is set', function () {
    foreach (['session:a', 'session:b', 'session:c'] as $session) {
        ScoringFixture::event(ScoringFixture::search('trust', $session, [1, 2]), 1, 1, 'click');
    }
    ScoringFixture::event(ScoringFixture::search('trust', 'cart:9', [1, 2]), 2, 1, 'click');
    $known = ScoringFixture::search('trust', 'session:d', [1, 2]);
    $known->forceFill(['customer_id' => 42])->save();
    ScoringFixture::event($known, 2, 1, 'click');

    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, guardConfig(['trusted_sessions_only' => true]));

    expect(scoresFor('trust'))->toBe([2 => 2.0]);

    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, guardConfig(['trusted_sessions_only' => false]));

    expect(scoresFor('trust'))->toBe([1 => 3.0, 2 => 2.0]);
});

it('never learns an excluded product and drops its score immediately', function () {
    foreach (['cart:1', 'cart:2'] as $session) {
        $search = ScoringFixture::search('ex', $session, [1, 2]);
        ScoringFixture::event($search, 1, 1, 'click');
        ScoringFixture::event($search, 2, 2, 'click');
    }
    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, guardConfig());
    expect(array_keys(scoresFor('ex')))->toBe([1, 2]);

    app(Overrides::class)->exclude(Product::class, 'ex', 1);

    expect(array_keys(scoresFor('ex')))->toBe([2])
        ->and(app(Overrides::class)->excluded(Product::class, 'ex')->all())->toBe([1]);

    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, guardConfig());
    expect(array_keys(scoresFor('ex')))->toBe([2]);

    app(Overrides::class)->include(Product::class, 'ex', 1);
    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, guardConfig());
    expect(array_keys(scoresFor('ex')))->toBe([1, 2]);
});

it('discards everything learned before a reset', function () {
    ScoringFixture::event(ScoringFixture::search('rs', 'cart:1', [1, 2]), 1, 1, 'click', at: now()->subHour());
    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, guardConfig());
    expect(array_keys(scoresFor('rs')))->toBe([1]);

    $this->travel(1)->minutes();
    app(Overrides::class)->reset(Product::class, 'rs');
    expect(scoresFor('rs'))->toBe([])
        ->and(app(Overrides::class)->resetAt(Product::class, 'rs'))->not->toBeNull()
        ->and(LearningOverride::query()->where('type', 'reset')->count())->toBe(1);

    $this->travel(1)->minutes();
    ScoringFixture::event(ScoringFixture::search('rs', 'cart:2', [1, 2]), 2, 1, 'click');
    app(PhpScoreAggregator::class)->aggregate(ScoringFixture::VERSION, guardConfig());

    expect(array_keys(scoresFor('rs')))->toBe([2]);
});
