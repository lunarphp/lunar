<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Tables\ProductsTable;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.tables
 */
class ProductsTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_table()
    {
        Livewire::test(ProductsTable::class);
    }

    /** @test */
    public function can_see_table_data()
    {
        $products = Product::factory(10)->create();
        Livewire::test(ProductsTable::class)->assertCanSeeTableRecords($products);
    }

    /** @test */
    public function can_see_base_columns()
    {
        $products = Product::factory(10)->create();

        $columns = [
            'status',
            'thumbnail',
            'name',
            'brand',
            'productType.name',
            'sku',
        ];

        $table = Livewire::test(ProductsTable::class);

        foreach ($columns as $column) {
            $table->assertCanRenderTableColumn($column);
        }
    }
}
