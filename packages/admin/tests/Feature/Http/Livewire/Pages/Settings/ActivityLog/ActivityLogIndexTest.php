<?php

namespace GetCandy\Hub\Tests\Feature\Http\Livewire\Pages\Settings\ActivityLog;

use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group hub.activity-log
 */
class ActivityLogIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get('/hub/settings/activity-log')
            ->assertRedirect(
                route('hub.login')
            );
    }

    /** @test */
    public function can_view_page_when_authenticated()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $this->actingAs($staff, 'staff');

        $this->get('/hub/settings/activity-log')
            ->assertSeeLivewire('hub.components.settings.activity-log.index');
    }
}
