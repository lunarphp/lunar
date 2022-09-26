<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Settings\Tables;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\TaxZonesTable;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\TaxZone;

/**
 * @group hub.tables
 */
class TaxZonesTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_component()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(TaxZonesTable::class)
            ->assertViewIs('l-tables::index');
    }

    /** @test */
    public function can_see_columns_and_data()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        TaxZone::factory(5)->create();

        $component = Livewire::actingAs($staff, 'staff')->test(TaxZonesTable::class)
                        ->assertViewIs('l-tables::index');

        foreach ($component->get('columns') as $column) {
            $component->assertSee($column->getHeading());
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $component->get('rows'));
        $this->assertCount(TaxZone::count(), $component->get('rows'));
    }
}
