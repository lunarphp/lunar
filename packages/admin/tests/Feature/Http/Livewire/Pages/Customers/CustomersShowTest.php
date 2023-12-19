<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Products;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\Customer;

/**
 * @group hub.customers
 */
class CustomersShowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create([
            'default' => true,
        ]);
    }

    /** @test */
    public function cant_view_page_as_guest()
    {
        $this->get(route('hub.customers.show', Customer::factory()->create()))
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

        $this->get(route('hub.customers.show', Customer::factory()->create()))
            ->assertStatus(403);
    }

    /** @test */
    public function can_view_page_with_correct_permission()
    {
        $this->setupRolesPermissions();

        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->givePermissionTo('catalogue:manage-customers');

        $this->actingAs($staff, 'staff');

        $this->get(route('hub.customers.show', Customer::factory()->create()))
            ->assertSeeLivewire('hub.components.customers.show');
    }
}
