<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Tables\StaffTable;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.tables
 */
class StaffTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_table()
    {
        Livewire::test(StaffTable::class);
    }

    /** @test */
    public function can_see_table_data()
    {
        $records = Staff::factory(10)->create();
        Livewire::test(StaffTable::class)->assertCanSeeTableRecords($records);
    }

    /** @test */
    public function can_see_base_columns()
    {
        Staff::factory(10)->create();

        $columns = [
            'admin',
            'firstname',
            'lastname',
            'email',
        ];

        $table = Livewire::test(StaffTable::class);

        foreach ($columns as $column) {
            $table->assertCanRenderTableColumn($column);
        }
    }
}
