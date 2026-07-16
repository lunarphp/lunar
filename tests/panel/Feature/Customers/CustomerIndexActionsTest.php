<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('ships first-party row actions and resolves their per-row urls', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Ordered by Position: edit (priority 10) before delete (priority 90).
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('tableActions.1.confirmation', 'Are you sure you want to delete this customer?')
            ->where('customers.data.0._actions.edit', route('panel.customers.edit', $customer))
            ->where('customers.data.0._actions.delete', route('panel.customers.destroy', $customer))
        );
});
