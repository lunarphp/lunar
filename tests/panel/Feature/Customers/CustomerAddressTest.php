<?php

use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('adds an address to a customer', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $country = Country::factory()->create();

    $this->post(route('panel.customers.addresses.store', $customer), [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'line_one' => '1 Analytical Engine Way',
        'city' => 'London',
        'country_id' => $country->id,
        'contact_email' => 'ada@example.com',
    ])->assertRedirect()
        ->assertSessionHas('success', 'Address added.');

    $address = $customer->addresses()->sole();

    expect($address->first_name)->toBe('Ada')
        ->and($address->city)->toBe('London')
        ->and($address->contact_email)->toBe('ada@example.com');
});

it('validates required fields when adding an address', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->post(route('panel.customers.addresses.store', $customer), [])
        ->assertSessionHasErrors(['first_name', 'last_name', 'line_one', 'city', 'country_id']);
});

it('updates an address', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $address = Address::factory()->for($customer)->create();
    $country = Country::factory()->create();

    $this->put(route('panel.customers.addresses.update', [$customer, $address]), [
        'first_name' => 'Updated',
        'last_name' => $address->last_name,
        'line_one' => 'New street',
        'city' => 'Manchester',
        'country_id' => $country->id,
    ])->assertRedirect()
        ->assertSessionHas('success', 'Address updated.');

    $address->refresh();

    expect($address->first_name)->toBe('Updated')
        ->and($address->line_one)->toBe('New street')
        ->and($address->city)->toBe('Manchester')
        ->and($address->country_id)->toBe($country->id);
});

it('deletes an address', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $address = Address::factory()->for($customer)->create();

    $this->delete(route('panel.customers.addresses.destroy', [$customer, $address]))
        ->assertRedirect()
        ->assertSessionHas('success', 'Address deleted.');

    expect(Address::find($address->id))->toBeNull();
});
