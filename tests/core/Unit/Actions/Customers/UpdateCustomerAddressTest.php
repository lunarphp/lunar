<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Customers\UpdateCustomerAddress;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the address attributes', function () {
    $customer = Customer::factory()->create();
    $address = Address::factory()->create([
        'customer_id' => $customer->id,
        'city' => 'Malibu',
    ]);

    $updated = app(UpdateCustomerAddress::class)->execute($address, [
        'city' => 'New York',
    ]);

    expect($updated->id)->toBe($address->id);

    $this->assertDatabaseHas('lunar_addresses', [
        'id' => $address->id,
        'city' => 'New York',
    ]);
});
