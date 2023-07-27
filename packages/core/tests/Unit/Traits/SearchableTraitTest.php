<?php

namespace Lunar\Tests\Unit\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Models\Product;
use Lunar\Tests\TestCase;

/**
 * @group traits
 */
class SearchableTraitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_add_searchable_fields()
    {
        Event::fake();

        $product = Product::factory()->create();

        $product->addSearchableAttribute('foo', 'bar');

        $data = $product->toSearchableArray();

        $this->assertEquals($data['foo'], 'bar');

        Event::assertDispatched('eloquent.indexing: '.get_class($product));
    }

    /** @test */
    public function can_remove_searchable_fields()
    {
        Event::fake();

        $product = Product::factory()->create();

        $product->addSearchableAttribute('foo', 'bar');

        $data = $product->toSearchableArray();

        $this->assertEquals($data['foo'], 'bar');

        $product->removeSearchableAttribute('foo');

        $data = $product->toSearchableArray();

        $this->assertArrayNotHasKey('foo', $data);

        Event::assertDispatched('eloquent.indexing: '.get_class($product));
    }

    /** @test */
    public function can_add_filterable_fields()
    {
        Event::fake();

        $product = Product::factory()->create();

        $product->addFilterableAttributes([
            'filter_one',
            'filter_two',
        ]);

        $data = $product->getFilterableAttributes();

        $this->assertContains('filter_one', $data);
        $this->assertContains('filter_two', $data);

        Event::assertDispatched('eloquent.searchSetup: '.get_class($product));
    }

    /** @test */
    public function can_remove_filterable_fields()
    {
        Event::fake();

        $product = Product::factory()->create();

        $product->addFilterableAttributes([
            'filter_one',
            'filter_two',
        ]);

        $product->removeFilterableAttributes([
            'filter_two',
        ]);

        $data = $product->getFilterableAttributes();

        $this->assertContains('filter_one', $data);
        $this->assertNotContains('filter_two', $data);

        Event::assertDispatched('eloquent.searchSetup: '.get_class($product));
    }

    /** @test */
    public function can_add_sortable_fields()
    {
        Event::fake();

        $product = Product::factory()->create();

        $product->addSortableAttributes([
            'sort_one',
            'sort_two',
        ]);

        $data = $product->getSortableAttributes();

        $this->assertContains('sort_one', $data);
        $this->assertContains('sort_two', $data);

        Event::assertDispatched('eloquent.searchSetup: '.get_class($product));
    }

    /** @test */
    public function can_remove_sortable_fields()
    {
        Event::fake();

        $product = Product::factory()->create();

        $product->addSortableAttributes([
            'sort_one',
            'sort_two',
        ]);

        $product->removeSortableAttributes([
            'sort_one',
        ]);

        $data = $product->getSortableAttributes();

        $this->assertNotContains('sort_one', $data);
        $this->assertContains('sort_two', $data);

        Event::assertDispatched('eloquent.searchSetup: '.get_class($product));
    }
}
