<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Actions\Carts\MergeCart;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can merge cart', function () {
    $customerGroups = CustomerGroup::factory(2)->create();

    $taxClass = TaxClass::factory()->create([
        'name' => 'Foobar',
    ]);

    $taxClass->taxRateAmounts()->create(
        TaxRateAmount::factory()->make([
            'percentage' => 20,
            'tax_class_id' => $taxClass->id,
        ])->toArray()
    );

    $cartA = Cart::factory()->hasCurrency(
        Currency::factory()->create([
            'decimal_places' => 2,
        ])
    )->create();

    $cartB = Cart::factory()->hasCurrency(
        Currency::factory()->create([
            'decimal_places' => 2,
        ])
    )->create();

    $purchasableA = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 1,
    ]);

    $purchasableB = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cartA->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $cartB->lines()->createMany([
        [
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ],
        [
            'purchasable_type' => get_class($purchasableB),
            'purchasable_id' => $purchasableB->id,
            'quantity' => 1,
        ],
    ]);

    app(MergeCart::class)->execute($cartA, $cartB);

    $cartA = $cartA->refresh();
    $cartB = $cartB->refresh();

    expect($cartB->merged_id)->toEqual($cartA->id);
    expect($cartA->lines)->toHaveCount(2);

    expect($cartA->lines->first(fn ($line) => $line->purchasable_id == $purchasableA->id)->quantity)->toEqual(2);
});

test('can handle merging of lines with different metas', function () {
    $taxClass = TaxClass::factory()->create([
        'name' => 'Foobar',
    ]);

    $taxClass->taxRateAmounts()->create(
        TaxRateAmount::factory()->make([
            'percentage' => 20,
            'tax_class_id' => $taxClass->id,
        ])->toArray()
    );

    $cartA = Cart::factory()->hasCurrency(
        Currency::factory()->create([
            'decimal_places' => 2,
        ])
    )->create();

    $cartB = Cart::factory()->hasCurrency(
        Currency::factory()->create([
            'decimal_places' => 2,
        ])
    )->create();

    $purchasableA = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 1,
    ]);

    $purchasableB = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cartA->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
        'meta' => [
            'foo' => 'bar',
        ],
    ]);

    $cartB->lines()->createMany([
        [
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
            'meta' => [
                'bar' => 'baz',
            ],
        ],
        [
            'purchasable_type' => get_class($purchasableB),
            'purchasable_id' => $purchasableB->id,
            'quantity' => 1,
        ],
    ]);

    app(MergeCart::class)->execute($cartA, $cartB);

    $cartA = $cartA->refresh();
    $cartB = $cartB->refresh();

    expect($cartB->merged_id)->toEqual($cartA->id);
    expect($cartA->lines)->toHaveCount(3);
});
