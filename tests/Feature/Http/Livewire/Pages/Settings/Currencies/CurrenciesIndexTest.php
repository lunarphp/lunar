<?php

namespace GetCandy\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Currencies;

use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group hub.currencies
 */
class CurrenciesIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get(route('hub.currencies.index'))
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

        $this->get(route('hub.currencies.index'))
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
                'handle' => 'settings:core',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.currencies.index'))
            ->assertSeeLivewire('hub.components.settings.currencies.index');
    }
}
