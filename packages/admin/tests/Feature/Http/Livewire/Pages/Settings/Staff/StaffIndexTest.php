<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Channels;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.staff
 */
class StaffIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get(route('hub.staff.index'))
            ->assertRedirect(
                route('hub.login')
            );
    }

    /** @test */
    public function cant_view_page_without_permission()
    {
        $this->setupRolesPermissions();

        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.staff.index'))
            ->assertStatus(403);
    }

    /** @test */
    public function can_view_page_with_correct_permission()
    {
        $this->setupRolesPermissions();

        $staff = Staff::factory()->create();

        $staff->givePermissionTo('settings', 'settings:manage-staff');

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.staff.index'))
            ->assertSeeLivewire('hub.components.settings.staff.index');
    }
}
