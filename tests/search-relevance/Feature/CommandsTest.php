<?php

use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\Scoring\Replay;
use Lunar\Tests\SearchRelevance\Support\ScoringFixture;
use Lunar\Tests\SearchRelevance\TestCase;

use function Pest\Laravel\artisan;

uses(TestCase::class)->group('search-relevance');

it('scores every configured model version', function () {
    // Sessions must hold a cart to count under the default trusted_sessions_only guard.
    foreach (['cart:1', 'cart:2', 'cart:3'] as $session) {
        ScoringFixture::event(ScoringFixture::search('w', $session, [1]), 1, 1, 'click');
    }

    artisan('lunar:search-relevance:score')
        ->expectsOutputToContain('[n1:database:keyword] wrote 1 query/product rows.')
        ->assertSuccessful();

    expect(SearchQueryScore::query()->sole())->toMatchArray(['normalised_query' => 'w', 'product_id' => 1, 'sessions' => 3]);
});

it('prunes searches and events past retention', function () {
    $old = ScoringFixture::search('old', 's1', [1], at: now()->subDays(401));
    ScoringFixture::event($old, 1, 1, 'click', at: now()->subDays(401));
    $recent = ScoringFixture::search('recent', 's1', [1]);
    ScoringFixture::event($recent, 1, 1, 'click');

    artisan('lunar:search-relevance:prune')
        ->expectsOutputToContain('Pruned 1 searches and 1 events')
        ->assertSuccessful();

    expect(SearchQuery::query()->pluck('id')->all())->toBe([$recent->id])
        ->and(SearchEvent::query()->count())->toBe(1);
});

it('replays purchases against the shown and ranked orders', function () {
    $improved = SearchQuery::factory()->create(['shown' => [1, 2, 3], 'ranked' => [3, 1, 2]]);
    ScoringFixture::event($improved, 3, 3, 'purchase');
    $worsened = SearchQuery::factory()->create(['shown' => [1, 2, 3], 'ranked' => [2, 3, 1]]);
    ScoringFixture::event($worsened, 1, 1, 'purchase');
    $same = SearchQuery::factory()->create(['shown' => [1, 2, 3], 'ranked' => [1, 3, 2]]);
    ScoringFixture::event($same, 1, 1, 'purchase');
    $notRanked = SearchQuery::factory()->create(['shown' => [1, 2, 3], 'ranked' => null]);
    ScoringFixture::event($notRanked, 2, 2, 'purchase');
    $noPurchase = SearchQuery::factory()->create(['shown' => [1, 2, 3], 'ranked' => [3, 2, 1]]);
    ScoringFixture::event($noPurchase, 3, 3, 'click');
    $tooOld = SearchQuery::factory()->create(['shown' => [1, 2, 3], 'ranked' => [3, 2, 1], 'created_at' => now()->subDays(40)]);
    ScoringFixture::event($tooOld, 3, 3, 'purchase');

    $result = app(Replay::class)->run(now()->subDays(30));

    expect($result->searches)->toBe(3)
        ->and($result->mrrShown)->toEqualWithDelta((1 / 3 + 1 + 1) / 3, 0.001)
        ->and($result->mrrRanked)->toEqualWithDelta((1 + 1 / 3 + 1) / 3, 0.001)
        ->and($result->improvedShare)->toEqualWithDelta(1 / 3, 0.001)
        ->and($result->worsenedShare)->toEqualWithDelta(1 / 3, 0.001);

    artisan('lunar:search-relevance:replay', ['--days' => 30])
        ->expectsOutputToContain('Searches with a purchase')
        ->expectsOutputToContain('33.3%')
        ->assertSuccessful();
});
