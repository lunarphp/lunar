<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Product;
use Lunar\Shipping\Models\ShippingExclusion;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class, RefreshDatabase::class)->group('shipping');

test('an exclusion belongs to its exclusion list', function () {
    $list = ShippingExclusionList::factory()->create();
    $product = Product::factory()->create();

    $exclusion = ShippingExclusion::factory()->create([
        'shipping_exclusion_list_id' => $list->id,
        'purchasable_id' => $product->id,
        'purchasable_type' => $product->getMorphClass(),
    ]);

    expect($exclusion->list)->toBeInstanceOf(ShippingExclusionList::class)
        ->and($exclusion->list->id)->toBe($list->id)
        ->and($list->exclusions()->count())->toBe(1);
});
