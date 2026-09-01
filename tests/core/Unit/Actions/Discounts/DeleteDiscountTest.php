<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Actions\Discounts\DeleteDiscount;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

/**
 * Row counts are read straight off the pivot tables. Going through the inverse
 * relations would join back to lunar_discounts and hide orphaned rows, which is
 * the failure these tests exist to catch.
 */
function pivotRowsFor(Discount $discount, string $table): int
{
    return DB::table(config('lunar.database.table_prefix').$table)
        ->where('discount_id', $discount->id)
        ->count();
}

test('deletes the discount', function () {
    $discount = Discount::factory()->create();

    app(DeleteDiscount::class)->execute($discount);

    expect(Discount::find($discount->id))->toBeNull();
});

test('leaves no targeting or audience rows behind', function () {
    Channel::factory()->create(['default' => true]);

    $discount = Discount::factory()->create();

    $discount->collections()->attach(Collection::factory()->create()->id, ['type' => 'limitation']);
    $discount->brands()->attach(Brand::factory()->create()->id, ['type' => 'limitation']);
    $discount->customers()->attach(Customer::factory()->create()->id);
    $discount->customerGroups()->attach(CustomerGroup::factory()->create()->id, [
        'enabled' => true,
        'visible' => true,
    ]);
    $discount->discountables()->create([
        'discountable_type' => Product::morphName(),
        'discountable_id' => Product::factory()->create()->id,
        'type' => 'limitation',
    ]);

    app(DeleteDiscount::class)->execute($discount);

    expect(pivotRowsFor($discount, 'collection_discount'))->toBe(0);
    expect(pivotRowsFor($discount, 'brand_discount'))->toBe(0);
    expect(pivotRowsFor($discount, 'customer_discount'))->toBe(0);
    expect(pivotRowsFor($discount, 'customer_group_discount'))->toBe(0);
    expect(pivotRowsFor($discount, 'discountables'))->toBe(0);
});

test('leaves no channel availability rows behind', function () {
    // channelables is a morph pivot with no foreign key, so nothing in the
    // schema clears it — the discount's own teardown has to.
    Channel::factory()->create(['default' => true]);

    $discount = Discount::factory()->create();
    $prefix = config('lunar.database.table_prefix');

    $channelables = fn () => DB::table($prefix.'channelables')
        ->where('channelable_type', Discount::morphName())
        ->where('channelable_id', $discount->id)
        ->count();

    expect($channelables())->toBe(1);

    app(DeleteDiscount::class)->execute($discount);

    expect($channelables())->toBe(0);
});
