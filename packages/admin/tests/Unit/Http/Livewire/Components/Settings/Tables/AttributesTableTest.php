<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Settings\Tables;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Lunar\Facades\AttributeManifest;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\AttributesTable;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.tables
 */
class AttributesTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_mount_component()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(AttributesTable::class)
            ->assertViewIs('l-tables::index');
    }

    /** @test */
    public function can_see_columns_and_data()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $types = AttributeManifest::getTypes();

        $component = Livewire::actingAs($staff, 'staff')->test(AttributesTable::class)
            ->assertViewIs('l-tables::index');

        foreach ($component->get('columns') as $column) {
            $component->assertSee($column->getHeading());
        }

        $this->assertInstanceOf(Collection::class, $component->get('rows'));
        $this->assertCount($types->count(), $component->get('rows'));
    }
}
