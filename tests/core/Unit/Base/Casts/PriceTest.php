<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\Exceptions\InvalidDataTypeValueException;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

function priceFor(array $attributes = []): Price
{
    $currency = Currency::factory()->create(['decimal_places' => 2, 'default' => true]);
    $variant = ProductVariant::factory()->create();

    return Price::factory()->make(array_merge([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'min_quantity' => 1,
    ], $attributes));
}

test('can not set a price to a fraction of a minor unit', function (string $key) {
    // The column counts minor units, so 12.99 is not "twelve pounds ninety-nine"
    // - it is twelve and a half of the smallest unit there is, which the column
    // rounds away.
    expect(fn () => priceFor([$key => 12.99]))
        ->toThrow(InvalidDataTypeValueException::class);
})->with(['price', 'compare_price']);

test('can say what the price was probably meant to be', function () {
    try {
        priceFor(['price' => 12.99]);
    } catch (InvalidDataTypeValueException $e) {
        expect($e->getMessage())->toContain('1299');

        return;
    }

    $this->fail('no exception was thrown');
});

test('can set a price to a whole number of minor units', function (int|float|string $given) {
    $price = priceFor(['price' => $given]);
    $price->save();

    expect(DB::table('lunar_prices')->where('id', $price->id)->value('price'))
        ->toEqual(1299);
})->with([
    'an integer' => 1299,
    'an integral float' => 1299.0,
    'a numeric string' => '1299',
]);

test('can leave a null compare price alone', function () {
    $price = priceFor(['price' => 1299, 'compare_price' => null]);
    $price->save();

    expect(DB::table('lunar_prices')->where('id', $price->id)->value('compare_price'))->toBeNull();
});
