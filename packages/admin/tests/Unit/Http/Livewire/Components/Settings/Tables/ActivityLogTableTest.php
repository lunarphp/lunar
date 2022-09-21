<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Settings\Tables;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\ActivityLogTable;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Channel;
use Spatie\Activitylog\Models\Activity;

/**
 * @group hub.tables
 */
class ActivityLogTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_component()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(ActivityLogTable::class)
            ->assertViewIs('tables::index');
    }

    /** @test */
    public function can_see_columns_and_data()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        activity()->enableLogging();

        $channels = Channel::factory(5)->create();

        $component = Livewire::actingAs($staff, 'staff')->test(ActivityLogTable::class)
                        ->assertViewIs('tables::index');

        foreach ($component->get('columns') as $column) {
            $component->assertSee($column->getHeading());
        }

        $this->assertInstanceOf(LengthAwarePaginator::class, $component->get('rows'));
        $this->assertCount(Activity::count(), $component->get('rows'));
    }
}
