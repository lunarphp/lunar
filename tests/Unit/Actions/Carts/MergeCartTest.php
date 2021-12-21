<?php

namespace GetCandy\Tests\Unit\Actions\Carts;

use GetCandy\Actions\Carts\CalculateLine;
use GetCandy\Actions\Carts\MergeCart;
use GetCandy\DataTypes\Price as DataTypesPrice;
use GetCandy\Models\Cart;
use GetCandy\Models\Currency;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use GetCandy\Models\TaxRateAmount;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

/**
 * @group getcandy.actions
 * @group getcandy.actions.carts
 */
class MergeCartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_merge_cart()
    {
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
            'tier' => 1,
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

        $this->assertEquals($cartA->id, $cartB->merged_id);
        $this->assertCount(2, $cartA->lines);

        $this->assertEquals(2, $cartA->lines->first(fn ($line) => $line->purchasable_id == $purchasableA->id)->quantity);
    }

    /** @test */
    public function can_handle_merging_of_lines_with_different_metas()
    {
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
            'tier' => 1,
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

        $this->assertEquals($cartA->id, $cartB->merged_id);
        $this->assertCount(3, $cartA->lines);
    }
}
