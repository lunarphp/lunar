<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Address;
use GetCandy\Models\Channel;
use GetCandy\Models\Customer;
use GetCandy\Models\CustomerGroup;
use GetCandy\Tests\Stubs\User;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

/**
 * @group getcandy.models
 */
class CustomerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_customer_with_minimum_attributes()
    {
        $customer = [
            'title' => null,
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'company_name' => null,
            'vat_no' => null,
            'meta' => null,
        ];

        Customer::create($customer);

        $this->assertDatabaseHas(
            'getcandy_customers',
            $customer
        );
    }

    /** @test */
    public function can_make_a_customer()
    {
        $customer = [
            'title' => 'Mr.',
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'company_name' => 'Stark Enterprises',
            'vat_no' => null,
            'meta' => null,
        ];

        Customer::create($customer);

        $this->assertDatabaseHas(
            'getcandy_customers',
            $customer
        );
    }

    /** @test */
    public function can_make_a_customer_with_meta_attribute()
    {
        $customer = [
            'title' => null,
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'company_name' => null,
            'vat_no' => null,
            'meta' => [
                'account' => 123456,
            ],
        ];

        $customer = Customer::create($customer);

        $this->assertEquals(123456, $customer->meta->account);
    }

    /** @test */
    public function can_get_full_name()
    {
        $customer = Customer::factory()->create([
            'title' => null,
            'first_name' => 'Tony',
            'last_name' => 'Stark',
        ]);

        $this->assertEquals(
            "$customer->first_name $customer->last_name",
            $customer->fullName
        );

        $customer = Customer::factory()->create([
            'title' => 'Mr.',
            'first_name' => 'Tony',
            'last_name' => 'Stark',
        ]);

        $this->assertEquals(
            "$customer->title $customer->first_name $customer->last_name",
            $customer->fullName
        );

        $customer = Customer::factory()->create([
            'title' => 'Mr.',
            'first_name' => '',
            'last_name' => 'Stark',
        ]);

        $this->assertEquals(
            "$customer->title $customer->last_name",
            $customer->fullName
        );

        $customer = Customer::factory()->create([
            'title' => 'Mr.',
            'first_name' => 'Tony',
            'last_name' => '',
        ]);

        $this->assertEquals(
            "$customer->title $customer->first_name",
            $customer->fullName
        );
    }

    /** @test */
    public function can_associate_to_customer_groups()
    {
        $groups = CustomerGroup::factory(4)->create();
        $customer = Customer::factory()->create();

        $customer->customerGroups()->sync($groups->pluck('id'));

        $this->assertCount($groups->count(), $customer->customerGroups);
    }

    /** @test */
    public function can_associate_to_users()
    {
        $users = User::factory(4)->create();
        $customer = Customer::factory()->create();

        $customer->users()->sync($users->pluck('id'));

        $this->assertCount($users->count(), $customer->users);
    }

    /** @test */
    public function can_fetch_customer_addresses()
    {
        $customer = Customer::factory()->create();
        $addresses = Address::factory(2)->create([
            'customer_id' => $customer->id,
        ]);

        $this->assertCount($addresses->count(), $customer->addresses()->get());
    }
}
