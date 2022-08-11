<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Tables\ProductTypesTable;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.tables
 */
class ProductTypesTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_table()
    {
        Livewire::test(ProductTypesTable::class);
    }

    /** @test */
    public function can_see_table_data()
    {
        $records = ProductType::factory(10)->create();
        Livewire::test(ProductTypesTable::class)->assertCanSeeTableRecords($records);
    }

    /** @test */
    public function can_see_base_columns()
    {
        ProductType::factory(10)->create();

        $columns = [
          'name',
          'mapped_attributes_count',
          'products_count',
        ];

        $table = Livewire::test(ProductTypesTable::class);

        foreach ($columns as $column) {
            $table->assertCanRenderTableColumn($column);
        }
    }
}
