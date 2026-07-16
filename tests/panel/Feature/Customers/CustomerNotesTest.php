<?php

use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('updates the customer notes', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create(['notes' => null]);

    $this->put(route('panel.customers.notes.update', $customer), [
        'notes' => 'Contact via email only; no marketing calls.',
    ])->assertRedirect()
        ->assertSessionHas('success', 'Notes updated.');

    expect($customer->refresh()->notes)->toBe('Contact via email only; no marketing calls.');
});

it('clears the customer notes when null is given', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create(['notes' => 'Old note.']);

    $this->put(route('panel.customers.notes.update', $customer), ['notes' => null]);

    expect($customer->refresh()->notes)->toBeNull();
});

it('leaves customer group membership untouched when updating notes', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $group = CustomerGroup::factory()->create();
    $customer->customerGroups()->sync([$group->id]);

    $this->put(route('panel.customers.notes.update', $customer), ['notes' => 'A note.']);

    expect($customer->customerGroups()->get()->pluck('id')->all())->toBe([$group->id]);
});

it('requires the notes field to be present', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->put(route('panel.customers.notes.update', $customer), [])
        ->assertSessionHasErrors(['notes']);
});

it('exposes the notes on the edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create(['notes' => 'VIP customer.']);

    $this->get(route('panel.customers.edit', $customer))
        ->assertInertia(fn ($page) => $page->where('customer.notes', 'VIP customer.'));
});
