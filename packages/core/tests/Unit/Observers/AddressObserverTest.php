<?php

namespace GetCandy\Tests\Unit\Observers;

use GetCandy\Models\Address;
use GetCandy\Models\Customer;
use GetCandy\Models\Language;
use GetCandy\Models\Url;
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
        $customerA = Customer::factory()->create();

        $addressADefault = Address::factory()->create([
            'customer_id' => $customerA->id,
            'shipping_default' => true,
        ]);

        $this->assertTrue($addressADefault->shipping_default);

        $addressANewDefault = Address::factory()->create([
            'customer_id' => $customerA->id,
            'shipping_default' => true,
        ]);

        $this->assertFalse($addressADefault->refresh()->shipping_default);
        $this->assertTrue($addressANewDefault->shipping_default);

        $customerB = Customer::factory()->create();

        $addressBDefault = Address::factory()->create([
            'customer_id' => $customerB->id,
            'shipping_default' => true,
        ]);

        $this->assertTrue($addressBDefault->shipping_default);

        $addressBNewDefault = Address::factory()->create([
            'customer_id' => $customerB->id,
            'shipping_default' => true,
        ]);

        $this->assertFalse($addressBDefault->refresh()->shipping_default);
        $this->assertTrue($addressBNewDefault->shipping_default);

        $this->assertFalse($addressADefault->refresh()->shipping_default);
        $this->assertTrue($addressANewDefault->refresh()->shipping_default);
    }

    /** @test */
    public function can_only_have_one_billing_default_per_customer()
    {
        $customerA = Customer::factory()->create();

        $addressADefault = Address::factory()->create([
            'customer_id' => $customerA->id,
            'billing_default' => true,
        ]);

        $this->assertTrue($addressADefault->billing_default);

        $addressANewDefault = Address::factory()->create([
            'customer_id' => $customerA->id,
            'billing_default' => true,
        ]);

        $this->assertFalse($addressADefault->refresh()->billing_default);
        $this->assertTrue($addressANewDefault->billing_default);

        $customerB = Customer::factory()->create();

        $addressBDefault = Address::factory()->create([
            'customer_id' => $customerB->id,
            'billing_default' => true,
        ]);

        $this->assertTrue($addressBDefault->billing_default);

        $addressBNewDefault = Address::factory()->create([
            'customer_id' => $customerB->id,
            'billing_default' => true,
        ]);

        $this->assertFalse($addressBDefault->refresh()->billing_default);
        $this->assertTrue($addressBNewDefault->billing_default);

        $this->assertFalse($addressADefault->refresh()->billing_default);
        $this->assertTrue($addressANewDefault->refresh()->billing_default);
    }
}
