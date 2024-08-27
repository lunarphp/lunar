<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Exceptions\SchedulingException;
use Lunar\Models\Channel;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Product;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can schedule using single model', function () {
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
});

test('can schedule always available', function () {
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
            'starts_at' => null,
            'ends_at' => null,
        ],
    );
});

test('can schedule using array of models', function () {
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
});

test('can schedule using collection of models', function () {
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
});

test('throws exception if non customer group provided', function () {
    $product = Product::factory()->create();

    CustomerGroup::factory(2)->create();

    Channel::factory(2)->create();

    $this->expectException(SchedulingException::class);

    $product->scheduleCustomerGroup(Channel::get());
})->group('testerr');

test('can schedule using array of ids', function () {
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
});

test('can schedule using collection of ids', function () {
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
});

test('can scope results to a customer group', function () {
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
    $resultD = Product::customerGroup()->get();
    $resultE = Product::customerGroup([])->get();
    $resultF = Product::customerGroup(collect())->get();

    expect($resultA)->toHaveCount(1);
    expect($resultB)->toHaveCount(1);
    expect($resultC)->toHaveCount(2);
    expect($resultD)->toHaveCount(2);
    expect($resultE)->toHaveCount(2);
    expect($resultF)->toHaveCount(2);

    expect($resultA->first()->id)->toEqual($productA->id);
    expect($resultB->first()->id)->toEqual($productB->id);

    $productA->customerGroups()->syncWithPivotValues([$groupA->id], [
        'starts_at' => now(),
        'enabled' => false,
        'visible' => false,
        'ends_at' => now()->addDay(),
    ]);

    expect(Product::customerGroup($groupA)->get())->toHaveCount(0);

    $productA->customerGroups()->syncWithPivotValues([$groupA->id], [
        'starts_at' => null,
        'enabled' => true,
        'visible' => true,
        'ends_at' => now()->addDay(),
    ]);

    expect(Product::customerGroup($groupA)->get())->toHaveCount(1);

    $productA->customerGroups()->syncWithPivotValues([$groupA->id], [
        'starts_at' => now()->subDay(),
        'enabled' => true,
        'visible' => true,
        'ends_at' => now()->subHour(),
    ]);

    expect(Product::customerGroup($groupA)->get())->toHaveCount(0);

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

    expect(Product::customerGroup($groupA)->get())->toHaveCount(0);

    expect(Product::customerGroup($groupA, $startsAt, $endsAt)->get())->toHaveCount(1);
});

test('customer groups are synced on model creation', function () {
    $customerGroup = CustomerGroup::factory()->create();
    $product = Product::factory()->create();

    $product->scheduleCustomerGroup($customerGroup);

    \Pest\Laravel\assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 1,
            'visible' => 1,
            'purchasable' => 1,
        ],
    );
});
