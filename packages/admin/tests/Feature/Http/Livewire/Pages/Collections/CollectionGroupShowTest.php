<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Products;

use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\CollectionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group hub.collections
 */
class CollectionGroupShowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get(route('hub.collection-groups.show', [
            'group' => CollectionGroup::factory()->create(),
        ]))->assertRedirect(
            route('hub.login')
        );
    }

    /** @test */
    public function cant_view_page_without_permission()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.collection-groups.show', [
            'group' => CollectionGroup::factory()->create(),
        ]))->assertStatus(403);
    }

    /** @test */
    public function can_view_page_with_correct_permission()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->permissions()->createMany([
            [
                'handle' => 'catalogue:manage-collections',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.collection-groups.show', [
            'group' => CollectionGroup::factory()->create(),
        ]))->assertSeeLivewire('hub.components.collections.collection-groups.show');
    }
}
