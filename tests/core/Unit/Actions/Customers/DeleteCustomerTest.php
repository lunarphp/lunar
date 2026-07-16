<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Customers\DeleteCustomer;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes the customer', function () {
    $customer = Customer::factory()->create();

    app(DeleteCustomer::class)->execute($customer);

    $this->assertDatabaseMissing('lunar_customers', ['id' => $customer->id]);
});
