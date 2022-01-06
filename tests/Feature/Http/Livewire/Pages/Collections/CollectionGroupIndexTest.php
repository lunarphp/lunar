<?php

namespace GetCandy\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Products;

use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\CollectionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group hub.collections
 */
class CollectionGroupIndexTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get(route('hub.collection-groups.index'))
            ->assertRedirect(
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

        $this->get(route('hub.collection-groups.index'))
            ->assertStatus(403);
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

        $this->get(route('hub.collection-groups.index'))
            ->assertSeeLivewire('hub.components.collections.collection-groups.index');
    }

    /** @test */
    public function will_redirect_to_collection_group_if_exists()
    {
        $staff = Staff::factory()->create();

        $staff->permissions()->createMany([
            [
                'handle' => 'catalogue:manage-collections',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $group = CollectionGroup::factory()->create();

        $this->get(route('hub.collection-groups.index'))
            ->assertRedirect(route('hub.collection-groups.show', $group->id));
    }
}
