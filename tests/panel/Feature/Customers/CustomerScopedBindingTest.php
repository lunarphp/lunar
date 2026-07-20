<?php

use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/**
 * Regression: ModelManifest's explicit route binders used to bypass
 * Route::scopeBindings(), so a nested sub-resource resolved even when it
 * belonged to a different parent record.
 */
it('scopes address bindings to the customer', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $mine = Customer::factory()->create();
    $other = Customer::factory()->create();
    $foreign = Address::factory()->create(['customer_id' => $other->id]);

    $this->delete(route('panel.customers.addresses.destroy', [$mine, $foreign]))
        ->assertNotFound();

    expect(Address::whereKey($foreign->id)->exists())->toBeTrue();
});
