<?php

namespace GetCandy\Tests\Unit\Actions\Carts;

use GetCandy\Actions\Carts\CalculateLine;
use GetCandy\Base\DataTransferObjects\TaxBreakdown;
use GetCandy\DataTypes\Price as DataTypesPrice;
use GetCandy\Models\Cart;
use GetCandy\Models\Currency;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Price;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use GetCandy\Models\TaxRateAmount;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.actions
 * @group getcandy.actions.carts
 */
class CalculateLineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_calculate_line()
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
                'percentage'   => 20,
                'tax_class_id' => $taxClass->id,
            ])->toArray()
        );

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id'  => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        Price::factory()->create([
            'price'          => 100,
            'currency_id'    => $currency->id,
            'tier'           => 1,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id'   => $purchasable->id,
            'quantity'         => 1,
        ]);

        $line = app(CalculateLine::class)->execute(
            $cart->lines()->first(),
            $customerGroups
        );

        $this->assertInstanceOf(DataTypesPrice::class, $line->unitPrice);
        $this->assertEquals(100, $line->unitPrice->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->subTotal);
        $this->assertEquals(100, $line->subTotal->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->taxAmount);
        $this->assertEquals(20, $line->taxAmount->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->total);
        $this->assertEquals(120, $line->total->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->discountTotal);
        $this->assertEquals(0, $line->discountTotal->value);

        $this->assertInstanceOf(TaxBreakdown::class, $line->taxBreakdown);
        $this->assertCount(1, $line->taxBreakdown->amounts);
    }

    /** @test */
    public function can_calculate_multi_unit_quantity_line()
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
                'percentage'   => 20,
                'tax_class_id' => $taxClass->id,
            ])->toArray()
        );

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id'  => $taxClass->id,
            'unit_quantity' => 2,
        ]);

        Price::factory()->create([
            'price'          => 100,
            'tier'           => 1,
            'currency_id'    => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id'   => $purchasable->id,
            'quantity'         => 1,
        ]);

        $line = app(CalculateLine::class)->execute(
            $cart->lines()->first(),
            $customerGroups
        );

        $this->assertInstanceOf(DataTypesPrice::class, $line->unitPrice);
        $this->assertEquals(50, $line->unitPrice->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->subTotal);
        $this->assertEquals(50, $line->subTotal->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->taxAmount);
        $this->assertEquals(10, $line->taxAmount->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->total);
        $this->assertEquals(60, $line->total->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->discountTotal);
        $this->assertEquals(0, $line->discountTotal->value);

        $this->assertInstanceOf(TaxBreakdown::class, $line->taxBreakdown);
        $this->assertCount(1, $line->taxBreakdown->amounts);
    }

    /** @test */
    public function can_calculate_large_unit_quantity_line()
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
                'percentage'   => 20,
                'tax_class_id' => $taxClass->id,
            ])->toArray()
        );

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id'  => $taxClass->id,
            'unit_quantity' => 100,
        ]);

        Price::factory()->create([
            'price'          => 1000,
            'tier'           => 1,
            'currency_id'    => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id'   => $purchasable->id,
            'quantity'         => 1,
        ]);

        $line = app(CalculateLine::class)->execute(
            $cart->lines()->first(),
            $customerGroups
        );

        $this->assertInstanceOf(DataTypesPrice::class, $line->unitPrice);
        $this->assertEquals(10, $line->unitPrice->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->subTotal);
        $this->assertEquals(10, $line->subTotal->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->taxAmount);
        $this->assertEquals(2, $line->taxAmount->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->total);
        $this->assertEquals(12, $line->total->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->discountTotal);
        $this->assertEquals(0, $line->discountTotal->value);

        $this->assertInstanceOf(TaxBreakdown::class, $line->taxBreakdown);
        $this->assertCount(1, $line->taxBreakdown->amounts);
    }

    /** @test */
    public function can_calculate_multiple_quantities()
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
                'percentage'   => 20,
                'tax_class_id' => $taxClass->id,
            ])->toArray()
        );

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id'  => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        Price::factory()->create([
            'price'          => 100,
            'tier'           => 1,
            'currency_id'    => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id'   => $purchasable->id,
            'quantity'         => 10,
        ]);

        $line = app(CalculateLine::class)->execute(
            $cart->lines()->first(),
            $customerGroups
        );

        $this->assertInstanceOf(DataTypesPrice::class, $line->unitPrice);
        $this->assertEquals(100, $line->unitPrice->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->subTotal);
        $this->assertEquals(1000, $line->subTotal->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->taxAmount);
        $this->assertEquals(200, $line->taxAmount->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->total);
        $this->assertEquals(1200, $line->total->value);

        $this->assertInstanceOf(DataTypesPrice::class, $line->discountTotal);
        $this->assertEquals(0, $line->discountTotal->value);

        $this->assertInstanceOf(TaxBreakdown::class, $line->taxBreakdown);
        $this->assertCount(1, $line->taxBreakdown->amounts);
    }
}
