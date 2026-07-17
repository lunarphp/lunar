<?php

use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('updates the customer notes', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create(['admin_notes' => null]);

    $this->put(route('panel.customers.notes.update', $customer), [
        'admin_notes' => 'Contact via email only; no marketing calls.',
    ])->assertRedirect()
        ->assertSessionHas('success', 'Admin notes updated.');

    expect($customer->refresh()->admin_notes)->toBe('Contact via email only; no marketing calls.');
});

it('clears the customer notes when null is given', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create(['admin_notes' => 'Old note.']);

    $this->put(route('panel.customers.notes.update', $customer), ['admin_notes' => null]);

    expect($customer->refresh()->admin_notes)->toBeNull();
});

it('leaves customer group membership untouched when updating notes', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $group = CustomerGroup::factory()->create();
    $customer->customerGroups()->sync([$group->id]);

    $this->put(route('panel.customers.notes.update', $customer), ['admin_notes' => 'A note.']);

    expect($customer->customerGroups()->get()->pluck('id')->all())->toBe([$group->id]);
});

it('requires the notes field to be present', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->put(route('panel.customers.notes.update', $customer), [])
        ->assertSessionHasErrors(['admin_notes']);
});

it('exposes the notes on the edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create(['admin_notes' => 'VIP customer.']);

    $this->get(route('panel.customers.edit', $customer))
        ->assertInertia(fn ($page) => $page->where('customer.admin_notes', 'VIP customer.'));
});
