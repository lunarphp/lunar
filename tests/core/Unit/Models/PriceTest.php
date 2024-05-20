<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Support\Facades\Config;
use Lunar\DataTypes\Price as DataTypesPrice;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can create a price', function () {
    $variant = ProductVariant::factory()->create();

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $data = [
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 123,
        'min_quantity' => 1,
    ];

    Price::factory()->create($data);

    $this->assertDatabaseHas((new Price())->getTable(), $data);
});

test('price is cast to a datatype', function () {
    $variant = ProductVariant::factory()->create();

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 123,
        'min_quantity' => 1,
    ]);

    expect($price->price)->toBeInstanceOf(DataTypesPrice::class);
});

/** @test  */
function can_handle_non_int_values()
{
    $variant = ProductVariant::factory()->create();

    $currencyGBP = Currency::factory()->create([
        'decimal_places' => 2,
        'code' => 'GBP',
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currencyGBP->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 12.99,
        'min_quantity' => 1,
    ]);

    expect($price->price->value)->toEqual(1299);
    expect($price->price->decimal)->toEqual(12.99);
    expect($price->price->formatted('en-gb'))->toEqual('£12.99');

    $currencyUSD = Currency::factory()->create([
        'decimal_places' => 3,
        'code' => 'USD',
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currencyUSD->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 12.995,
        'min_quantity' => 1,
    ]);

    expect($price->price->value)->toEqual(12995);
    expect($price->price->decimal)->toEqual(12.995);
    expect($price->price->formatted('en-us'))->toEqual('$12.995');

    $price = Price::factory()->create([
        'currency_id' => $currencyGBP->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 1299,
        'min_quantity' => 1,
    ]);

    expect($price->price->value)->toEqual(1299);
    expect($price->price->decimal)->toEqual(12.99);
    expect($price->price->formatted('en-gb'))->toEqual('£12.99');

    $currencyEUR = Currency::factory()->create([
        'decimal_places' => 3,
        'code' => 'EUR',
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currencyEUR->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => '1,250.950',
        'min_quantity' => 1,
    ]);

    expect($price->price->value)->toEqual(1250950);
    expect($price->price->decimal)->toEqual(1250.95);
    expect($price->price->formatted('en_gb'))->toEqual('€1,250.950');

    $price = Price::factory()->create([
        'currency_id' => $currencyEUR->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => '1,250.955',
        'min_quantity' => 1,
    ]);

    expect($price->price->value)->toEqual(1250955);
    expect($price->price->decimal)->toEqual(1250.955);
    expect($price->price->formatted('en_gb'))->toEqual('€1,250.955');
}

test('compare price is cast correctly', function () {
    $variant = ProductVariant::factory()->create();

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
        'code' => 'GBP',
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 12.99,
        'compare_price' => 13.99,
        'min_quantity' => 1,
    ]);

    expect($price->compare_price)->toBeInstanceOf(DataTypesPrice::class);

    expect($price->compare_price->value)->toEqual(1399);
    expect($price->compare_price->decimal)->toEqual(13.99);
    expect($price->compare_price->formatted('en_gb'))->toEqual('£13.99');
});

test('can get a price', function () {
    $variant = ProductVariant::factory()->create();

    $currencyUSD = Currency::factory()->create([
        'code' => 'USD',
        'decimal_places' => 2,
        'default' => true,
    ]);

    $currencyGBP = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
        'default' => false,
    ]);

    $customerGroup = CustomerGroup::factory()->make();
    $customerGroup->save();

    Price::factory()->create([
        'currency_id' => $currencyUSD->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 123,
        'min_quantity' => 1,
    ]);

    Price::factory()->create([
        'currency_id' => $currencyGBP->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 99,
        'min_quantity' => 1,
    ]);

    Price::factory()->create([
        'currency_id' => $currencyUSD->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 101,
        'min_quantity' => 5,
    ]);

    Price::factory()->create([
        'currency_id' => $currencyUSD->id,
        'customer_group_id' => $customerGroup->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 75,
        'min_quantity' => 1,
    ]);

    // Check we get the default currency price
    $price = $variant->pricing()->get();
    expect($price->matched->price->decimal)->toEqual(1.23);

    // Check we get a tier price
    $price = $variant->pricing()->qty(6)->guest()->get();
    expect($price->matched->price->decimal)->toEqual(1.01);

    // Check we get a price for GBP
    $price = $variant->pricing()->qty(6)->currency($currencyGBP)->get();
    expect($price->matched->price->decimal)->toEqual(0.99);

    // Check we get a price for a customer group
    $price = $variant->pricing()
        ->qty(1)
        ->currency(null)
        ->customerGroup($customerGroup)
        ->get();
    expect($price->matched->price->decimal)->toEqual(0.75);
});

test('can get a price ex tax', function () {
    Config::set('lunar.pricing.stored_inclusive_of_tax', true);

    $variant = ProductVariant::factory()->create();

    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
        'default' => true,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 999,
        'min_quantity' => 1,
    ]);

    expect($price->priceExTax()->value)->toEqual(833);
});

test('can get a price inc tax', function () {
    Config::set('lunar.pricing.stored_inclusive_of_tax', false);

    $variant = ProductVariant::factory()->create();

    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
        'default' => true,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
        'price' => 833,
        'min_quantity' => 1,
    ]);

    expect($price->priceIncTax()->value)->toEqual(1000);
});
