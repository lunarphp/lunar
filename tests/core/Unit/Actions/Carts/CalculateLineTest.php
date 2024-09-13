<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Actions\Carts\CalculateLine;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\DataTypes\Price as DataTypesPrice;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can calculate line', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);
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

    $purchasable = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 100,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $line = app(CalculateLine::class)->execute(
        $cart->lines()->first(),
        $customerGroups
    );

    expect($line->unitPrice)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->unitPrice->value)->toEqual(100);

    expect($line->subTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->subTotal->value)->toEqual(100);

    expect($line->taxAmount)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->taxAmount->value)->toEqual(20);

    expect($line->total)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->total->value)->toEqual(120);

    expect($line->discountTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->discountTotal->value)->toEqual(0);

    expect($line->taxBreakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($line->taxBreakdown->amounts)->toHaveCount(1);
});

test('can calculate multi unit quantity line', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

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

    $purchasable = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 2,
    ]);

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $line = app(CalculateLine::class)->execute(
        $cart->lines()->first(),
        $customerGroups
    );

    expect($line->unitPrice)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->unitPrice->value)->toEqual(50);

    expect($line->subTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->subTotal->value)->toEqual(50);

    expect($line->taxAmount)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->taxAmount->value)->toEqual(10);

    expect($line->total)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->total->value)->toEqual(60);

    expect($line->discountTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->discountTotal->value)->toEqual(0);

    expect($line->taxBreakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($line->taxBreakdown->amounts)->toHaveCount(1);
});

test('can calculate large unit quantity line', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

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

    $purchasable = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 100,
    ]);

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $line = app(CalculateLine::class)->execute(
        $cart->lines()->first(),
        $customerGroups
    );

    expect($line->unitPrice)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->unitPrice->value)->toEqual(10);

    expect($line->subTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->subTotal->value)->toEqual(10);

    expect($line->taxAmount)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->taxAmount->value)->toEqual(2);

    expect($line->total)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->total->value)->toEqual(12);

    expect($line->discountTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->discountTotal->value)->toEqual(0);

    expect($line->taxBreakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($line->taxBreakdown->amounts)->toHaveCount(1);
});

test('can calculate multiple quantities', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

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

    $purchasable = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 10,
    ]);

    $line = app(CalculateLine::class)->execute(
        $cart->lines()->first(),
        $customerGroups
    );

    expect($line->unitPrice)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->unitPrice->value)->toEqual(100);

    expect($line->subTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->subTotal->value)->toEqual(1000);

    expect($line->taxAmount)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->taxAmount->value)->toEqual(200);

    expect($line->total)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->total->value)->toEqual(1200);

    expect($line->discountTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->discountTotal->value)->toEqual(0);

    expect($line->taxBreakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($line->taxBreakdown->amounts)->toHaveCount(1);
});

/** @test * */
function check_for_know_rounding_error_on_unit_price_with_unit_quantity_of_one()
{
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);
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

    $purchasable = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 912, //Known failing value
        'currency_id' => $currency->id,
        'min_quantity' => 1,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $line = app(CalculateLine::class)->execute(
        $cart->lines()->first(),
        $customerGroups
    );

    expect($line->unitPrice)->toBeInstanceOf(DataTypesPrice::class);
    expect($line->unitPrice->value)->toEqual(912);
}
