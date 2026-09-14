<?php

use Illuminate\Container\Container;
use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Contracts\Signal;
use Lunar\SearchRelevance\DataObjects\RankingContext;
use Lunar\SearchRelevance\Signals\SignalCombiner;

uses()->group('search-relevance');

function relevanceContext(string $mode = 'on', ?string $sort = null, string $query = 'cable tie'): RankingContext
{
    return new RankingContext(Product::class, $query, 'session:x', null, $mode, $sort, md5('[]'), 'n1:database:keyword');
}

final class FixedSignal implements Signal
{
    /** @param array<int, float> $scores */
    public function __construct(private array $scores) {}

    public function scores(RankingContext $context, array $productIds): array
    {
        return array_intersect_key($this->scores, array_flip($productIds));
    }
}

it('sums weighted signals and clamps to 0..1', function () {
    $container = new Container;
    $container->bind('signal.a', fn () => new FixedSignal([1 => 0.5, 2 => 0.9, 3 => 0.2]));
    $container->bind('signal.b', fn () => new FixedSignal([1 => 0.5, 2 => 0.9, 4 => -2.0]));

    $combiner = new SignalCombiner(['signal.a' => 1.0, 'signal.b' => 0.5], $container);

    expect($combiner->combine(relevanceContext(), [1, 2, 3, 4]))->toBe([
        1 => 0.75,
        2 => 1.0,
        3 => 0.2,
        4 => 0.0,
    ]);
});

it('only scores the products asked for', function () {
    $container = new Container;
    $container->bind('signal.a', fn () => new FixedSignal([1 => 0.5, 2 => 0.9]));

    $combiner = new SignalCombiner(['signal.a' => 1.0], $container);

    expect($combiner->combine(relevanceContext(), [2]))->toBe([2 => 0.9]);
});
