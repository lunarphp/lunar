<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Customers\DeleteCustomerAddress;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes the address', function () {
    $customer = Customer::factory()->create();
    $address = Address::factory()->create(['customer_id' => $customer->id]);

    app(DeleteCustomerAddress::class)->execute($address);

    $this->assertDatabaseMissing('lunar_addresses', ['id' => $address->id]);
});
