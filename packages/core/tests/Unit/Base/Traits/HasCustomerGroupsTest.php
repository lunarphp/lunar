<?php

namespace Lunar\Tests\Unit\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Exceptions\SchedulingException;
use Lunar\Models\Channel;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Product;
use Lunar\Tests\TestCase;

/**
 * @group lunar.traits
 */
class HasCustomerGroupsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_schedule_using_single_model()
    {
        $product = Product::factory()->create();

        $customerGroup = CustomerGroup::factory()->create();

        $product->scheduleCustomerGroup($customerGroup);

        $this->assertDatabaseHas(
            'lunar_customer_group_product',
            [
                'customer_group_id' => $customerGroup->id,
                'enabled'           => 1,
                'visible'           => 1,
                'purchasable'       => 1,
            ],
        );
    }

    /** @test */
    public function can_schedule_using_array_of_models()
    {
        $product = Product::factory()->create();

        $groups = CustomerGroup::factory(2)->create();

        $product->scheduleCustomerGroup([$groups->first(), $groups->last()]);

        foreach ($groups as $group) {
            $this->assertDatabaseHas(
                'lunar_customer_group_product',
                [
                    'customer_group_id' => $group->id,
                    'enabled'           => 1,
                    'visible'           => 1,
                    'purchasable'       => 1,
                ]
            );
        }
    }

    /** @test */
    public function can_schedule_using_collection_of_models()
    {
        $product = Product::factory()->create();

        $groups = CustomerGroup::factory(2)->create();

        $product->scheduleCustomerGroup($groups);

        foreach ($groups as $group) {
            $this->assertDatabaseHas(
                'lunar_customer_group_product',
                [
                    'customer_group_id' => $group->id,
                    'enabled'           => 1,
                    'visible'           => 1,
                    'purchasable'       => 1,
                ]
            );
        }
    }

    /**
     * @test
     * @group testerr
     * */
    public function throws_exception_if_non_customer_group_provided()
    {
        $product = Product::factory()->create();

        CustomerGroup::factory(2)->create();

        Channel::factory(2)->create();

        $this->expectException(SchedulingException::class);

        $product->scheduleCustomerGroup(Channel::get());
    }

    /** @test */
    public function can_schedule_using_array_of_ids()
    {
        $product = Product::factory()->create();

        $groups = CustomerGroup::factory(2)->create();

        $product->scheduleCustomerGroup($groups->pluck('id')->toArray());

        foreach ($groups as $group) {
            $this->assertDatabaseHas(
                'lunar_customer_group_product',
                [
                    'customer_group_id' => $group->id,
                    'enabled'           => 1,
                    'visible'           => 1,
                    'purchasable'       => 1,
                ]
            );
        }
    }

    /** @test */
    public function can_schedule_using_collection_of_ids()
    {
        $product = Product::factory()->create();

        $groups = CustomerGroup::factory(2)->create();

        $product->scheduleCustomerGroup($groups->pluck('id')->toArray());

        foreach ($groups as $group) {
            $this->assertDatabaseHas(
                'lunar_customer_group_product',
                [
                    'customer_group_id' => $group->id,
                    'enabled'           => 1,
                    'visible'           => 1,
                    'purchasable'       => 1,
                ]
            );
        }
    }
}
