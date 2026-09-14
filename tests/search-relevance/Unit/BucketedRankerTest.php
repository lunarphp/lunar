<?php

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Contracts\Signal;
use Lunar\SearchRelevance\Data\Hit;
use Lunar\SearchRelevance\Data\HitCollection;
use Lunar\SearchRelevance\Data\RankingContext;
use Lunar\SearchRelevance\Rankers\BucketedRanker;
use Lunar\SearchRelevance\Rankers\NullRanker;
use Lunar\SearchRelevance\Signals\SignalCombiner;

uses()->group('search-relevance');

final class BoostSignal implements Signal
{
    /** @param array<int, float> $scores */
    public function __construct(private array $scores) {}

    public function scores(RankingContext $context, array $productIds): array
    {
        return array_intersect_key($this->scores, array_flip($productIds));
    }
}

function rankerContext(string $mode = 'on', ?string $sort = null, string $query = 'cable tie'): RankingContext
{
    return new RankingContext(Product::class, $query, 'session:x', null, $mode, $sort, md5('[]'), 'n1:database:keyword');
}

function hitsFor(array $ids): HitCollection
{
    return new HitCollection(array_map(fn ($id, $i) => new Hit($id, $i + 1, 0.0, ['id' => $id]), $ids, array_keys($ids)));
}

function bucketedRanker(array $scores, int $bucketSize = 3): BucketedRanker
{
    $container = new Container;
    $container->bind('signal', fn () => new BoostSignal($scores));

    return new BucketedRanker(
        new SignalCombiner(['signal' => 1.0], $container),
        new Repository(['lunar' => ['search_relevance' => ['bucket_size' => $bucketSize]]]),
    );
}

it('reorders within buckets only, preserving engine order on ties', function () {
    $ranker = bucketedRanker([6 => 1.0, 3 => 0.5, 2 => 0.5]);

    $ranked = $ranker->rank(rankerContext(), hitsFor([1, 2, 3, 4, 5, 6, 7]));

    // Bucket one: 2 and 3 tie at 0.5 so engine order decides; bucket two: 6 wins; 7 alone.
    expect($ranked->productIds())->toBe([2, 3, 1, 6, 4, 5, 7])
        ->and($ranked->first()->boost)->toBe(0.5)
        ->and($ranked[2]->boost)->toBe(0.0);
});

it('leaves the order alone when the context says not to rank', function () {
    $ranker = bucketedRanker([3 => 1.0]);

    expect($ranker->rank(rankerContext(mode: 'off'), hitsFor([1, 2, 3]))->productIds())->toBe([1, 2, 3])
        ->and($ranker->rank(rankerContext(sort: 'price:asc'), hitsFor([1, 2, 3]))->productIds())->toBe([1, 2, 3])
        ->and($ranker->rank(rankerContext(query: '*'), hitsFor([1, 2, 3]))->productIds())->toBe([1, 2, 3])
        ->and($ranker->rank(rankerContext(), hitsFor([]))->productIds())->toBe([]);
});

it('ranks in shadow mode so the ranked order can be logged', function () {
    $ranker = bucketedRanker([3 => 1.0]);

    expect($ranker->rank(rankerContext(mode: 'shadow'), hitsFor([1, 2, 3]))->productIds())->toBe([3, 1, 2]);
});

it('the null ranker returns the hits untouched', function () {
    $hits = hitsFor([1, 2, 3]);

    expect((new NullRanker)->rank(rankerContext(), $hits))->toBe($hits);
});

it('round-trips hits through arrays for the cache', function () {
    $hit = new Hit(5, 2, 1.5, ['id' => 5], 'learned');
    $hit->boost = 0.4;

    $restored = Hit::fromArray($hit->toArray());

    expect($restored->productId)->toBe(5)
        ->and($restored->originalPosition)->toBe(2)
        ->and($restored->score)->toBe(1.5)
        ->and($restored->source)->toBe('learned')
        ->and($restored->boost)->toBe(0.4)
        ->and(HitCollection::fromArrays((new HitCollection([$hit]))->toArrays())->productIds())->toBe([5]);
});
