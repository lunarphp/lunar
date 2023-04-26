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
                'enabled' => 1,
                'visible' => 1,
                'purchasable' => 1,
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
                    'enabled' => 1,
                    'visible' => 1,
                    'purchasable' => 1,
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
                    'enabled' => 1,
                    'visible' => 1,
                    'purchasable' => 1,
                ]
            );
        }
    }

    /**
     * @test
     *
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
                    'enabled' => 1,
                    'visible' => 1,
                    'purchasable' => 1,
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
                    'enabled' => 1,
                    'visible' => 1,
                    'purchasable' => 1,
                ]
            );
        }
    }


    /** @test */
    public function can_scope_results_to_a_customer_group()
    {
        $groupA = CustomerGroup::factory()->create([
            'handle' => 'group-a',
        ]);

        $groupB = CustomerGroup::factory()->create([
            'handle' => 'group-b',
        ]);

        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $productA->customerGroups()->syncWithPivotValues([$groupA->id], [
            'starts_at' => now(),
            'enabled' => true,
            'visible' => true,
            'ends_at' => now()->addDay(),
        ]);

        $productB->customerGroups()->syncWithPivotValues([$groupB->id], [
            'starts_at' => now(),
            'enabled' => true,
            'visible' => true,
            'ends_at' => now()->addDay(),
        ]);

        $this->assertDatabaseHas($productA->customerGroups()->getTable(), [
            'product_id' => $productA->id,
            'starts_at' => now(),
            'ends_at' => now()->addDay(),
            'enabled' => true,
        ]);

        $resultA = Product::customerGroup($groupA)->get();
        $resultB = Product::customerGroup($groupB)->get();
        $resultC = Product::customerGroup([$groupA, $groupB])->get();

        $this->assertCount(1, $resultA);
        $this->assertCount(1, $resultB);
        $this->assertCount(2, $resultC);

        $this->assertEquals($productA->id, $resultA->first()->id);
        $this->assertEquals($productB->id, $resultB->first()->id);

        $productA->customerGroups()->syncWithPivotValues([$groupA->id], [
            'starts_at' => now(),
            'enabled' => false,
            'ends_at' => now()->addDay(),
        ]);

        $this->assertCount(0, Product::customerGroup($groupA)->get());

        $productA->customerGroups()->syncWithPivotValues([$groupA->id], [
            'starts_at' => null,
            'enabled' => true,
            'visible' => true,
            'ends_at' => now()->addDay(),
        ]);

        $this->assertCount(1, Product::customerGroup($groupA)->get());

        $productA->customerGroups()->syncWithPivotValues([$groupA->id], [
            'starts_at' => now()->subDay(),
            'enabled' => true,
            'visible' => true,
            'ends_at' => now()->subHour(),
        ]);

        $this->assertCount(0, Product::customerGroup($groupA)->get());

        $startsAt = now()->addDay();
        $endsAt = now()->addDays(2);

        $productA->customerGroups()->syncWithPivotValues([$groupA->id], [
            'starts_at' => $startsAt,
            'enabled' => true,
            'visible' => true,
            'ends_at' => $endsAt,
        ]);

        $this->assertDatabaseHas($productA->customerGroups()->getTable(), [
            'product_id' => $productA->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'enabled' => true,
            'visible' => true,
        ]);

        $this->assertCount(0, Product::customerGroup($groupA)->get());

        $this->assertCount(1, Product::customerGroup($groupA, $startsAt, $endsAt)->get());
    }
}
