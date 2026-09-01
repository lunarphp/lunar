<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Actions\Discounts\UpdateDiscount;
use Lunar\Core\Exceptions\DiscountActionException;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Discountable;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

/**
 * @return array<string, int[]> the discountable rows for a bucket, keyed by morph name
 */
function discountablesFor(Discount $discount, string $bucket): array
{
    return Discountable::whereDiscountId($discount->id)
        ->whereType($bucket)
        ->get()
        ->groupBy('discountable_type')
        ->map(fn ($rows) => $rows->pluck('discountable_id')->sort()->values()->all())
        ->all();
}

test('updates the given attributes', function () {
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [
        'name' => 'Winter Sale',
        'handle' => 'winter-sale',
        'priority' => 5,
        'stop' => true,
        'data' => ['percentage' => 20],
    ]);

    $discount->refresh();

    expect($discount->name)->toBe('Winter Sale');
    expect($discount->handle)->toBe('winter-sale');
    expect($discount->priority)->toBe(5);
    expect((bool) $discount->stop)->toBeTrue();
    expect($discount->data)->toBe(['percentage' => 20]);
});

test('syncs channel availability when given', function () {
    $channel = Channel::factory()->create();
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], channels: [
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->addDay(),
            'ends_at' => null,
        ],
    ]);

    $pivot = $discount->channels()->first()?->pivot;

    expect((bool) $pivot->enabled)->toBeTrue();
    expect($pivot->starts_at)->not->toBeNull();
});

test('syncs customer group availability when given', function () {
    $group = CustomerGroup::factory()->create();
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], customerGroups: [
        $group->id => [
            'enabled' => true,
            'visible' => false,
            'starts_at' => null,
            'ends_at' => null,
        ],
    ]);

    $pivot = $discount->customerGroups()->first()?->pivot;

    expect((bool) $pivot->enabled)->toBeTrue();
    expect((bool) $pivot->visible)->toBeFalse();
});

test('null leaves availability and targeting untouched', function () {
    $channel = Channel::factory()->create();
    $collection = Collection::factory()->create();
    $discount = Discount::factory()->create();

    $discount->channels()->sync([
        $channel->id => ['enabled' => true, 'starts_at' => null, 'ends_at' => null],
    ]);
    $discount->collections()->attach($collection->id, ['type' => 'limitation']);

    app(UpdateDiscount::class)->execute($discount, ['name' => 'Renamed']);

    expect((bool) $discount->channels()->first()->pivot->enabled)->toBeTrue();
    expect($discount->collections()->count())->toBe(1);
});

test('routes limitation and exclusion collections to the collection_discount pivot', function () {
    $limited = Collection::factory()->create();
    $excluded = Collection::factory()->create();
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'limitation' => ['collections' => [$limited->id]],
        'exclusion' => ['collections' => [$excluded->id]],
    ]);

    $pivots = $discount->collections()->get()->mapWithKeys(
        fn ($collection) => [$collection->pivot->type => $collection->id]
    );

    expect($pivots->all())->toEqual([
        'limitation' => $limited->id,
        'exclusion' => $excluded->id,
    ]);
    expect(Discountable::whereDiscountId($discount->id)->count())->toBe(0);
});

test('routes condition and reward collections to discountables', function () {
    // BuyXGetY reads its condition and reward collections from the morph table,
    // not the pivot the line-targeting types read.
    $condition = Collection::factory()->create();
    $reward = Collection::factory()->create();
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'condition' => ['collections' => [$condition->id]],
        'reward' => ['collections' => [$reward->id]],
    ]);

    expect(discountablesFor($discount, 'condition'))->toBe([
        Collection::morphName() => [$condition->id],
    ]);
    expect(discountablesFor($discount, 'reward'))->toBe([
        Collection::morphName() => [$reward->id],
    ]);
    expect($discount->collections()->count())->toBe(0);
});

test('routes products and variants to discountables under their own morph', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'limitation' => ['products' => [$product->id], 'variants' => [$variant->id]],
    ]);

    expect(discountablesFor($discount, 'limitation'))->toBe([
        Product::morphName() => [$product->id],
        ProductVariant::morphName() => [$variant->id],
    ]);
});

test('routes brands to the brand_discount pivot with the bucket type', function () {
    $limited = Brand::factory()->create();
    $excluded = Brand::factory()->create();
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'limitation' => ['brands' => [$limited->id]],
        'exclusion' => ['brands' => [$excluded->id]],
    ]);

    $pivots = $discount->brands()->get()->mapWithKeys(
        fn ($brand) => [$brand->pivot->type => $brand->id]
    );

    expect($pivots->all())->toEqual([
        'limitation' => $limited->id,
        'exclusion' => $excluded->id,
    ]);
});

test('routes customers to the customer_discount pivot', function () {
    $customer = Customer::factory()->create();
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'limitation' => ['customers' => [$customer->id]],
    ]);

    expect($discount->customers()->get()->pluck('id')->all())->toBe([$customer->id]);
});

test('a present bucket replaces that bucket wholesale', function () {
    $keptProduct = Product::factory()->create();
    $droppedProduct = Product::factory()->create();
    $droppedCollection = Collection::factory()->create();
    $droppedCustomer = Customer::factory()->create();

    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'limitation' => [
            'products' => [$droppedProduct->id],
            'collections' => [$droppedCollection->id],
            'customers' => [$droppedCustomer->id],
        ],
    ]);

    // Only products this time: the kinds left out of a present bucket are cleared.
    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'limitation' => ['products' => [$keptProduct->id]],
    ]);

    expect(discountablesFor($discount, 'limitation'))->toBe([
        Product::morphName() => [$keptProduct->id],
    ]);
    expect($discount->collections()->count())->toBe(0);
    expect($discount->customers()->count())->toBe(0);
});

test('syncing one bucket leaves the other buckets alone', function () {
    $limited = Collection::factory()->create();
    $excluded = Collection::factory()->create();
    $rewarded = Product::factory()->create();
    $replacement = Collection::factory()->create();

    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'limitation' => ['collections' => [$limited->id]],
        'exclusion' => ['collections' => [$excluded->id]],
        'reward' => ['products' => [$rewarded->id]],
    ]);

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'limitation' => ['collections' => [$replacement->id]],
    ]);

    $pivots = $discount->collections()->get()->mapWithKeys(
        fn ($collection) => [$collection->pivot->type => $collection->id]
    );

    expect($pivots->all())->toEqual([
        'limitation' => $replacement->id,
        'exclusion' => $excluded->id,
    ]);
    expect(discountablesFor($discount, 'reward'))->toBe([
        Product::morphName() => [$rewarded->id],
    ]);
});

test('rejects an unknown target bucket', function () {
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'audience' => ['customers' => [1]],
    ]);
})->throws(DiscountActionException::class, 'Unknown discount target bucket [audience]');

test('rejects a kind the bucket cannot target', function () {
    $discount = Discount::factory()->create();

    app(UpdateDiscount::class)->execute($discount, [], targets: [
        'condition' => ['brands' => [1]],
    ]);
})->throws(DiscountActionException::class, 'The [condition] bucket cannot target brands');

test('guards the payload before writing anything', function () {
    $collection = Collection::factory()->create();
    $discount = Discount::factory()->create();

    try {
        app(UpdateDiscount::class)->execute($discount, ['name' => 'Renamed'], targets: [
            'limitation' => ['collections' => [$collection->id]],
            'reward' => ['brands' => [1]],
        ]);
    } catch (DiscountActionException) {
        // Expected — the assertions below are the point.
    }

    expect(Discount::find($discount->id)->name)->not->toBe('Renamed');
    expect($discount->collections()->count())->toBe(0);
});

test('rolls the whole update back when a targeting write fails', function () {
    $product = Product::factory()->create();
    $collection = Collection::factory()->create();
    $discount = Discount::factory()->create(['name' => 'Original']);

    // The pivot write is the failure point. SQLite runs with foreign keys off
    // here, so the constraint violation that would raise it on MySQL or
    // Postgres has to be provoked directly.
    DB::listen(function ($query) {
        if (str_contains($query->sql, 'collection_discount')) {
            throw new RuntimeException('pivot write failed');
        }
    });

    expect(fn () => app(UpdateDiscount::class)->execute($discount, ['name' => 'Renamed'], targets: [
        'limitation' => [
            'products' => [$product->id],
            'collections' => [$collection->id],
        ],
    ]))->toThrow(RuntimeException::class);

    expect(Discount::find($discount->id)->name)->toBe('Original');
    expect(Discountable::whereDiscountId($discount->id)->count())->toBe(0);
});
