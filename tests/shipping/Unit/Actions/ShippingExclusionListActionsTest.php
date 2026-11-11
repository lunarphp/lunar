<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\CreatesShippingExclusionList;
use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\DeletesShippingExclusionList;
use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\UpdatesShippingExclusionList;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class, RefreshDatabase::class)->group('shipping', 'shipping-actions');

test('a list can be created', function () {
    $list = app(CreatesShippingExclusionList::class)->execute(['name' => 'Bulky goods']);

    expect($list->name)->toBe('Bulky goods');
});

test('products replace the product exclusions and leave other morph rows alone', function () {
    $list = ShippingExclusionList::factory()->create();
    [$a, $b, $c] = Product::factory()->count(3)->create();
    $variant = ProductVariant::factory()->create();

    $list->exclusions()->create(['purchasable_type' => $a->getMorphClass(), 'purchasable_id' => $a->id]);
    $list->exclusions()->create(['purchasable_type' => $variant->getMorphClass(), 'purchasable_id' => $variant->id]);

    app(UpdatesShippingExclusionList::class)->execute($list, ['name' => 'Renamed', 'products' => [$b->id, $c->id]]);

    $productIds = $list->exclusions()->where('purchasable_type', $a->getMorphClass())->pluck('purchasable_id')->sort()->values()->all();

    expect($list->refresh()->name)->toBe('Renamed')
        ->and($productIds)->toBe(collect([$b->id, $c->id])->sort()->values()->all())
        ->and($list->exclusions()->where('purchasable_type', $variant->getMorphClass())->count())->toBe(1);
});

test('deleting a list removes its exclusions and detaches it from zones', function () {
    $list = ShippingExclusionList::factory()->create();
    $zone = ShippingZone::factory()->create();
    $zone->shippingExclusions()->sync([$list->id]);
    $product = Product::factory()->create();
    $list->exclusions()->create(['purchasable_type' => $product->getMorphClass(), 'purchasable_id' => $product->id]);

    app(DeletesShippingExclusionList::class)->execute($list);

    expect(ShippingExclusionList::find($list->id))->toBeNull()
        ->and($zone->shippingExclusions()->count())->toBe(0);
});
