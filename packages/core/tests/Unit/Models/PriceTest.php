<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\DataTypes\Price as DataTypesPrice;
use GetCandy\Models\Currency;
use GetCandy\Models\Customer;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Price;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\Stubs\User;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group prices
 */
class PriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_a_price()
    {
        $variant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
            'format'         => '£{value}',
        ]);

        $data = [
            'currency_id'    => $currency->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 123,
            'tier'           => 1,
        ];

        Price::factory()->create($data);

        $this->assertDatabaseHas((new Price())->getTable(), $data);
    }

    /** @test */
    public function price_is_cast_to_a_datatype()
    {
        $variant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
            'format'         => '£{value}',
        ]);

        $price = Price::factory()->create([
            'currency_id'    => $currency->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 123,
            'tier'           => 1,
        ]);

        $this->assertInstanceOf(DataTypesPrice::class, $price->price);
    }

    /** @test  */
    public function can_handle_non_int_values()
    {
        $variant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
            'format'         => '£{value}',
        ]);

        $price = Price::factory()->create([
            'currency_id'    => $currency->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 12.99,
            'tier'           => 1,
        ]);

        $this->assertEquals(1299, $price->price->value);
        $this->assertEquals(12.99, $price->price->decimal);
        $this->assertEquals('£12.99', $price->price->formatted);

        $currency = Currency::factory()->create([
            'decimal_places' => 3,
            'format'         => '£{value}',
        ]);

        $price = Price::factory()->create([
            'currency_id'    => $currency->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 12.995,
            'tier'           => 1,
        ]);

        $this->assertEquals(12995, $price->price->value);
        $this->assertEquals(12.995, $price->price->decimal);
        $this->assertEquals('£12.995', $price->price->formatted);

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
            'format'         => '£{value}',
        ]);

        $price = Price::factory()->create([
            'currency_id'    => $currency->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 1299,
            'tier'           => 1,
        ]);

        $this->assertEquals(1299, $price->price->value);
        $this->assertEquals(12.99, $price->price->decimal);
        $this->assertEquals('£12.99', $price->price->formatted);

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
            'format'         => '{value}DK',
        ]);

        $price = Price::factory()->create([
            'currency_id'    => $currency->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => '1,250.95',
            'tier'           => 1,
        ]);

        $this->assertEquals(125095, $price->price->value);
        $this->assertEquals(1250.95, $price->price->decimal);
        $this->assertEquals('1,250.95DK', $price->price->formatted);

        $currency = Currency::factory()->create([
            'decimal_places' => 3,
            'format'         => '£{value}',
        ]);

        $price = Price::factory()->create([
            'currency_id'    => $currency->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => '1,250.955',
            'tier'           => 1,
        ]);

        $this->assertEquals(1250955, $price->price->value);
        $this->assertEquals(1250.955, $price->price->decimal);
        $this->assertEquals('£1,250.955', $price->price->formatted);
    }

    /** @test */
    public function compare_price_is_cast_correctly()
    {
        $variant = ProductVariant::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
            'format'         => '£{value}',
        ]);

        $price = Price::factory()->create([
            'currency_id'    => $currency->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 12.99,
            'compare_price'  => 13.99,
            'tier'           => 1,
        ]);

        $this->assertInstanceOf(DataTypesPrice::class, $price->compare_price);

        $this->assertEquals(1399, $price->compare_price->value);
        $this->assertEquals(13.99, $price->compare_price->decimal);
        $this->assertEquals('£13.99', $price->compare_price->formatted);
    }

    /** @test */
    public function can_get_a_price()
    {
        $variant = ProductVariant::factory()->create();

        $currencyUSD = Currency::factory()->create([
            'code'           => 'USD',
            'decimal_places' => 2,
            'format'         => '£{value}',
            'default'        => true,
        ]);

        $currencyGBP = Currency::factory()->create([
            'code'           => 'GBP',
            'decimal_places' => 2,
            'format'         => '£{value}',
            'default'        => false,
        ]);

        $customerGroup = CustomerGroup::factory()->make();
        $customerGroup->save();

        Price::factory()->create([
            'currency_id'    => $currencyUSD->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 123,
            'tier'           => 1,
        ]);

        Price::factory()->create([
            'currency_id'    => $currencyGBP->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 99,
            'tier'           => 1,
        ]);

        Price::factory()->create([
            'currency_id'    => $currencyUSD->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 101,
            'tier'           => 5,
        ]);

        Price::factory()->create([
            'currency_id'    => $currencyUSD->id,
            'customer_group_id' => $customerGroup->id,
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
            'price'          => 75,
            'tier'           => 1,
        ]);

        // Check we get the default currency price
        $price = $variant->getPrice();
        $this->assertEquals(1.23, $price->matched->price->decimal);

        // Check we get a tier price
        $price = $variant->getPrice(6);
        $this->assertEquals(1.01, $price->matched->price->decimal);

        // Check we get a price for GBP
        $price = $variant->getPrice(6, $currencyGBP);
        $this->assertEquals(0.99, $price->matched->price->decimal);

        // Check we get a price for a customer group
        $price = $variant->getPrice(1, null, null, $customerGroup);
        $this->assertEquals(0.75, $price->matched->price->decimal);
    }
}
