<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\DatabaseEngine;
use Laravel\Scout\Engines\NullEngine;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Product;
use Lunar\Core\Search\ProductIndexer;
use Lunar\Core\Search\ScoutIndexer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

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

test('indexing a model costs no more queries than building its document', function () {
    app()->instance(EngineManager::class, (new EngineManager(app()))->extend('documents', fn () => new class extends NullEngine
    {
        public function update($models)
        {
            $models->each->toSearchableArray();
        }
    }));
    Config::set('scout.driver', 'documents');

    $id = Product::factory()->create()->id;

    $countQueries = function (Closure $callback) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    };

    $buildingTheDocument = $countQueries(fn () => Product::find($id)->toSearchableArray());
    $indexing = $countQueries(fn () => Product::find($id)->searchable());

    expect($indexing)->toBe($buildingTheDocument);
});
