<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\Tables\OrdersTable;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.tables
 */
class OrdersTableTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create([
            'default' => true,
        ]);

        Language::factory()->create([
            'default' => true,
        ]);
    }

    /** @test */
    public function can_mount_table()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(OrdersTable::class);
    }

    /** @test */
    public function can_see_table_data()
    {
        $records = Order::factory(10)->create();
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(OrdersTable::class)->assertCanSeeTableRecords($records);
    }

    /** @test */
    public function can_see_base_columns()
    {
        Order::factory(10)->create();

        $columns = [
            'status',
            'reference',
            'billingAddress.fullName',
            'billingAddress.company_name',
            'billingAddress.contact_email',
            'total',
            'placed_at',
        ];

        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $table = Livewire::actingAs($staff, 'staff')->test(OrdersTable::class);

        foreach ($columns as $column) {
            $table->assertCanRenderTableColumn($column);
        }
    }
}
