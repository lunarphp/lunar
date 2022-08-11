<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Tables\CustomersTable;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.tables
 */
class CustomersTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_table()
    {
        Livewire::test(CustomersTable::class);
    }

    /** @test */
    public function can_see_table_data()
    {
        $records = Customer::factory(10)->create();
        Livewire::test(CustomersTable::class)->assertCanSeeTableRecords($records);
    }

    /** @test */
    public function can_see_base_columns()
    {
        Customer::factory(10)->create();

        $columns = [
            'fullName',
            'company_name',
            'vat_no',
            'orders_count',
            'users_count',
        ];

        $table = Livewire::test(CustomersTable::class);

        foreach ($columns as $column) {
            $table->assertCanRenderTableColumn($column);
        }
    }
}
