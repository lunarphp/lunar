<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components;

use Lunar\Hub\DataTransferObjects\Search\Facets;
use Lunar\Hub\DataTransferObjects\Search\SearchResults;
use Lunar\Hub\Http\Livewire\Components\Orders\OrdersIndex;
use Lunar\Hub\Http\Livewire\Components\Orders\OrdersTable;
use Lunar\Hub\Models\SavedSearch;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Search\OrderSearch;
use Lunar\Hub\Tables\Builders\OrdersTableBuilder;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;
use Mockery;

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
    public function can_mount_component()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(OrdersTable::class)
            ->assertViewIs('tables::index');
    }

    /** @test */
    public function can_see_columns()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $columns = app(OrdersTableBuilder::class)->getColumns();

        $orders = Order::factory(5)->create();

        $component = Livewire::actingAs($staff, 'staff')->test(OrdersTable::class)
                        ->assertViewIs('tables::index');

        foreach ($columns as $column) {
            $component->assertSee($column->getHeading());
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $component->get('rows'));
        $this->assertCount($orders->count(), $component->get('rows'));
    }

    /** @test */
    public function can_save_search()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')
            ->test(OrdersTable::class)
            ->assertSet('savedSearchName', '')
            ->set('savedSearchName', 'foobar')
            ->call('saveSearch')
            ->assertHasNoErrors();

        $this->assertDatabaseHas((new SavedSearch)->getTable(), [
            'name' => 'foobar',
            'staff_id' => $staff->id,
        ]);
    }

    /** @test */
//     public function can_update_order_status()
//     {
//         $staff = Staff::factory()->create([
//             'admin' => true,
//         ]);
//
//         $order = Order::factory()->create([
//             'status' => 'unpaid',
//         ]);
//
//         $this->assertEquals('unpaid', $order->status);
//
//         Livewire::actingAs($staff, 'staff')
//             ->test(OrdersIndex::class)
//             ->set('selected', [$order->id])
//             ->set('status', 'paid')
//             ->call('updateStatus')
//             ->assertHasNoErrors();
//
//         $this->assertEquals('paid', $order->refresh()->status);
//     }
//
//     /** @test */
//     public function can_update_multiple_order_statuses()
//     {
//         $staff = Staff::factory()->create([
//             'admin' => true,
//         ]);
//
//         $orderA = Order::factory()->create([
//             'status' => 'unpaid',
//         ]);
//
//         $orderB = Order::factory()->create([
//             'status' => 'on-credit',
//         ]);
//
//         Livewire::actingAs($staff, 'staff')
//             ->test(OrdersIndex::class)
//             ->set('selected', [$orderA->id, $orderB->id])
//             ->set('status', 'dispatched')
//             ->call('updateStatus')
//             ->assertHasNoErrors();
//
//         $this->assertEquals('dispatched', $orderA->refresh()->status);
//         $this->assertEquals('dispatched', $orderB->refresh()->status);
//     }
}
