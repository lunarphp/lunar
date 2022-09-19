<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Channels;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.staff
 */
class StaffShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cant_view_page_as_guest()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->get(route('hub.staff.show', $staff->id))
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

        $this->get(route('hub.staff.show', $staff->id))
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
                'handle' => 'settings',
            ],
            [
                'handle' => 'settings:manage-staff',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.staff.show', $staff->id))
            ->assertSeeLivewire('hub.components.settings.staff.show');
    }
}
