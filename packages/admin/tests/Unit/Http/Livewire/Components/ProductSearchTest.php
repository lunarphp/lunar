<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components;

use Lunar\FieldTypes\Text;
use Lunar\Hub\Http\Livewire\Components\ProductSearch;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Product;
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
