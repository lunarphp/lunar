<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\FieldTypes\Text;
use GetCandy\Hub\Http\Livewire\Components\ProductSearch;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.components
 */
class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function component_mounts_correctly()
    {
        Product::factory()->create([
            'attribute_data' => collect([
                'name' => new Text('Test A'),
            ]),
        ]);

        Livewire::test(ProductSearch::class)
            ->assertSet('searchTerm', null)
            ->assertSet('selected', []);
    }
}
