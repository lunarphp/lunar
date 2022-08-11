<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Tables\TaxZonesTable;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\TaxZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.tables
 */
class TaxZonesTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_table()
    {
        Livewire::test(TaxZonesTable::class);
    }

    /** @test */
    public function can_see_table_data()
    {
        $records = TaxZone::factory(10)->create();
        Livewire::test(TaxZonesTable::class)->assertCanSeeTableRecords($records);
    }

    /** @test */
    public function can_see_base_columns()
    {
        TaxZone::factory(10)->create();

        $columns = [
          'default',
          'name',
          'zone_type',
          'active',
        ];

        $table = Livewire::test(TaxZonesTable::class);

        foreach ($columns as $column) {
            $table->assertCanRenderTableColumn($column);
        }
    }
}
