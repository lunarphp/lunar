<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Currencies;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;

/**
 * @group hub.currencies
 */
class CurrencyShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cant_view_page_as_guest()
    {
        $currency = Currency::factory()->create();

        $this->get(route('hub.currencies.show', $currency->id))
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
        $currency = Currency::factory()->create();

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.currencies.show', $currency->id))
            ->assertStatus(403);
    }

    /** @test */
    public function can_view_page_with_correct_permission()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);
        $currency = Currency::factory()->create();

        $staff->permissions()->createMany([
            [
                'handle' => 'settings',
            ],
            [
                'handle' => 'settings:core',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.currencies.show', $currency->id))
            ->assertSeeLivewire('hub.components.settings.currencies.show');
    }
}
