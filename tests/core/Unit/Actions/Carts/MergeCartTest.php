<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Lunar\Actions\Carts\MergeCart;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;
use Lunar\Tests\Core\Stubs\TestGiftCard;
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

test('can merge lines of different purchasable types that share an id', function () {
    if (! Schema::hasTable('test_gift_cards')) {
        Schema::create('test_gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });
    }

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
        'default' => true,
    ]);

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    // The id collision two purchasable tables produce on their own.
    $giftCard = TestGiftCard::create(['name' => 'Gift card']);
    TestGiftCard::query()->where('id', $giftCard->id)->update(['id' => $variant->id]);
    $giftCard = TestGiftCard::find($variant->id);

    $target = Cart::factory()->create(['currency_id' => $currency->id]);
    $source = Cart::factory()->create(['currency_id' => $currency->id]);

    $target->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    $source->lines()->create([
        'purchasable_type' => $giftCard->getMorphClass(),
        'purchasable_id' => $giftCard->id,
        'quantity' => 4,
    ]);

    (new MergeCart)->execute($target, $source);

    $lines = $target->refresh()->lines()->get()
        ->map(fn ($line) => $line->purchasable_type.' x'.$line->quantity)
        ->sort()
        ->values()
        ->all();

    // Two different products, so two lines - the gift card must not be folded
    // into the variant that happens to share its id.
    expect($lines)->toEqual([
        TestGiftCard::class.' x4',
        $variant->getMorphClass().' x1',
    ]);
});
