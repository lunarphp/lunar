<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Products;

use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group hub.customers
 */
class CustomersIndexTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get(route('hub.customers.index'))
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

        $this->get(route('hub.customers.index'))
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
                'handle' => 'catalogue:manage-customers',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.customers.index'))
            ->assertSeeLivewire('hub.components.customers.index');
    }
}
