<?php

namespace Lunar\Tests\Unit\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Engines\DatabaseEngine;
use Laravel\Scout\Engines\NullEngine;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Search\ProductIndexer;
use Lunar\Search\ScoutIndexer;
use Lunar\Tests\TestCase;

/**
 * @group traits
 */
class SearchableTraitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_correct_engine_mapping()
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(NullEngine::class, $product->searchableUsing());

        Config::set('lunar.search.engine_map', [
            Product::class => 'database',
        ]);

        $this->assertInstanceOf(DatabaseEngine::class, $product->searchableUsing());
    }

    /** @test */
    public function can_get_correct_indexer()
    {
        $product = Product::factory()->create();
        $collection = Collection::factory()->create();

        $this->assertInstanceOf(ProductIndexer::class, $product->indexer());
        $this->assertInstanceOf(ScoutIndexer::class, $collection->indexer());

        Config::set('lunar.search.indexers', [
            Product::class => ScoutIndexer::class,
        ]);

        $this->assertSame(ScoutIndexer::class, get_class($product->indexer()));
    }
}
