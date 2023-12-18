<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Engines\DatabaseEngine;
use Laravel\Scout\Engines\NullEngine;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Search\ProductIndexer;
use Lunar\Search\ScoutIndexer;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can get correct engine mapping', function () {
    $product = Product::factory()->create();

    expect($product->searchableUsing())->toBeInstanceOf(NullEngine::class);

    Config::set('lunar.search.engine_map', [
        Product::class => 'database',
    ]);

    expect($product->searchableUsing())->toBeInstanceOf(DatabaseEngine::class);
});

test('can get correct indexer', function () {
    $product = Product::factory()->create();
    $collection = Collection::factory()->create();

    expect($product->indexer())->toBeInstanceOf(ProductIndexer::class);
    expect($collection->indexer())->toBeInstanceOf(ScoutIndexer::class);

    Config::set('lunar.search.indexers', [
        Product::class => ScoutIndexer::class,
    ]);

    expect(get_class($product->indexer()))->toBe(ScoutIndexer::class);
});
