<?php

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Carts\MergeCart;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cartA->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $cartB->lines()->createMany([
        [
            'purchasable_type' => $purchasableA->getMorphClass(),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ],
        [
            'purchasable_type' => $purchasableB->getMorphClass(),
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cartA->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
        'meta' => [
            'foo' => 'bar',
        ],
    ]);

    $cartB->lines()->createMany([
        [
            'purchasable_type' => $purchasableA->getMorphClass(),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
            'meta' => [
                'bar' => 'baz',
            ],
        ],
        [
            'purchasable_type' => $purchasableB->getMorphClass(),
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

test('keeps a reward line from the source cart apart from the shopper line in the target', function () {
    $currency = Currency::factory()->create();

    $target = Cart::factory()->create(['currency_id' => $currency->id]);
    $source = Cart::factory()->create(['currency_id' => $currency->id]);

    $purchasable = ProductVariant::factory()->create(['unit_quantity' => 1]);

    $discount = Discount::factory()->create();

    $target->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $source->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
        'added_by_discount_id' => $discount->id,
    ]);

    app(MergeCart::class)->execute($target, $source);

    $lines = $target->refresh()->lines;

    expect($lines)->toHaveCount(2)
        ->and($lines->whereNull('added_by_discount_id')->first()->quantity)->toBe(2)
        ->and($lines->firstWhere('added_by_discount_id', $discount->id)->quantity)->toBe(1);
});

/**
 * A second purchasable type whose ids line up with product variants', as two
 * independent id sequences do.
 */
class MergeCartGiftCard extends ProductVariant
{
    protected $table = 'product_variants';
}

test('keeps lines of different purchasable types that share an id apart', function () {
    Relation::morphMap(['gift_card' => MergeCartGiftCard::class]);

    $currency = Currency::factory()->create();
    $target = Cart::factory()->create(['currency_id' => $currency->id]);
    $source = Cart::factory()->create(['currency_id' => $currency->id]);
    $variant = ProductVariant::factory()->create();

    $target->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    // Another purchasable type whose id sequence has reached the variant's id.
    $source->lines()->create([
        'purchasable_type' => 'gift_card',
        'purchasable_id' => $variant->id,
        'quantity' => 4,
    ]);

    (new MergeCart)->execute($target, $source->load('lines'));

    $lines = $target->lines()->orderBy('id')->get()
        ->map(fn ($line) => [$line->purchasable_type, $line->purchasable_id, $line->quantity])
        ->all();

    expect($lines)->toBe([
        [$variant->getMorphClass(), $variant->id, 1],
        ['gift_card', $variant->id, 4],
    ]);
});
