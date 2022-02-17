<?php

namespace GetCandy\Tests\Unit\Observers;

use GetCandy\Models\Address;
use GetCandy\Models\Customer;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group observers
 */
class AddressObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_only_have_one_shipping_default_per_customer()
    {
        $customer = Customer::factory()->create();

        $addressA = Address::factory()->create([
            'customer_id' => $customer->id,
            'shipping_default' => true,
        ]);

        $this->assertTrue($addressA->shipping_default);

        $addressB = Address::factory()->create([
            'customer_id' => $customer->id,
            'shipping_default' => true,
        ]);

        $this->assertFalse($addressA->refresh()->shipping_default);
        $this->assertTrue($addressB->shipping_default);

        $addressA->update([
            'shipping_default' => true,
        ]);

        $this->assertTrue($addressA->shipping_default);
        $this->assertFalse($addressB->refresh()->shipping_default);
    }

    /** @test */
    public function can_only_have_one_billing_default_per_customer()
    {
        $customer = Customer::factory()->create();

        $addressA = Address::factory()->create([
            'customer_id' => $customer->id,
            'billing_default' => true,
        ]);

        $this->assertTrue($addressA->billing_default);

        $addressB = Address::factory()->create([
            'customer_id' => $customer->id,
            'billing_default' => true,
        ]);

        $this->assertFalse($addressA->refresh()->billing_default);
        $this->assertTrue($addressB->billing_default);

        $addressA->update([
            'billing_default' => true,
        ]);

        $this->assertTrue($addressA->billing_default);
        $this->assertFalse($addressB->refresh()->billing_default);
    }
}
