<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\DataTypes\Price as DataTypesPrice;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can create a price', function () {
    $variant = ProductVariant::factory()->create();

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $data = [
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'price' => 123,
        'min_quantity' => 1,
    ];

    Price::factory()->create($data);

    $this->assertDatabaseHas((new Price)->getTable(), $data);
});

test('price is cast to a datatype', function () {
    $variant = ProductVariant::factory()->create();

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
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
        'priceable_type' => $variant->getMorphClass(),
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
        'priceable_type' => $variant->getMorphClass(),
        'price' => 12.995,
        'min_quantity' => 1,
    ]);

    expect($price->price->value)->toEqual(12995);
    expect($price->price->decimal)->toEqual(12.995);
    expect($price->price->formatted('en-us'))->toEqual('$12.995');

    $price = Price::factory()->create([
        'currency_id' => $currencyGBP->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
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
        'priceable_type' => $variant->getMorphClass(),
        'price' => '1,250.950',
        'min_quantity' => 1,
    ]);

    expect($price->price->value)->toEqual(1250950);
    expect($price->price->decimal)->toEqual(1250.95);
    expect($price->price->formatted('en_gb'))->toEqual('€1,250.950');

    $price = Price::factory()->create([
        'currency_id' => $currencyEUR->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
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
        'priceable_type' => $variant->getMorphClass(),
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
        'priceable_type' => $variant->getMorphClass(),
        'price' => 123,
        'min_quantity' => 1,
    ]);

    Price::factory()->create([
        'currency_id' => $currencyGBP->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'price' => 99,
        'min_quantity' => 1,
    ]);

    Price::factory()->create([
        'currency_id' => $currencyUSD->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'price' => 101,
        'min_quantity' => 5,
    ]);

    Price::factory()->create([
        'currency_id' => $currencyUSD->id,
        'customer_group_id' => $customerGroup->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
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
        'priceable_type' => $variant->getMorphClass(),
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
        'priceable_type' => $variant->getMorphClass(),
        'price' => 833,
        'min_quantity' => 1,
    ]);

    expect($price->priceIncTax()->value)->toEqual(1000);
});

test('can get a compare price inc tax', function () {
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
        'priceable_type' => $variant->getMorphClass(),
        'price' => 833,
        'compare_price' => 1667,
        'min_quantity' => 1,
    ]);

    expect($price->comparePriceIncTax()->value)->toEqual(2000);
});

test('priceIncTax falls back to the default tax zone when no zone is given', function () {
    Config::set('lunar.pricing.stored_inclusive_of_tax', false);

    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
        'default' => true,
    ]);

    $taxClass = TaxClass::factory()->create();

    // Default zone: 0 %
    $defaultTaxZone = TaxZone::factory()->state(['default' => true])->create();
    $defaultRate = TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])->create();
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => $defaultRate->id,
        'percentage' => 0,
    ]);

    // Override zone: 20 %
    $overrideZone = TaxZone::factory()->state(['default' => false])->create();
    $overrideRate = TaxRate::factory()->state(['tax_zone_id' => $overrideZone])->create();
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => $overrideRate->id,
        'percentage' => 20,
    ]);

    $variant = ProductVariant::factory(['tax_class_id' => $taxClass->id])->create();

    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'price' => 1000,
        'min_quantity' => 1,
    ]);

    // No param → falls back to the store's default zone (0 %).
    expect($price->priceIncTax()->value)->toEqual(1000);

    // Explicit override zone → 20 % applies.
    expect($price->priceIncTax($overrideZone)->value)->toEqual(1200);
});

test('priceIncTax explicit taxZone param overrides the default zone', function () {
    // Prices stored ex-tax.
    Config::set('lunar.pricing.stored_inclusive_of_tax', false);

    $currency = Currency::factory()->create(['code' => 'AED', 'decimal_places' => 2, 'default' => true]);

    $taxClass = TaxClass::factory()->create(['name' => 'Standard']);

    // Zone A: 5 % (default)
    $zoneA = TaxZone::factory()->state(['default' => true])->create(['name' => 'Zone A']);
    $rateA = TaxRate::factory()->state(['tax_zone_id' => $zoneA])->create();
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => $rateA->id,
        'percentage' => 5,
    ]);

    // Zone B: 20 %
    $zoneB = TaxZone::factory()->state(['default' => false])->create(['name' => 'Zone B']);
    $rateB = TaxRate::factory()->state(['tax_zone_id' => $zoneB])->create();
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => $rateB->id,
        'percentage' => 20,
    ]);

    $variant = ProductVariant::factory(['tax_class_id' => $taxClass->id])->create();
    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'price' => 1000,
        'min_quantity' => 1,
    ]);

    // Default would resolve to Zone A (5 %), but explicit Zone B (20 %) must win.
    expect($price->priceIncTax(taxZone: $zoneB)->value)->toEqual(1200);

    // Also works on priceExTax (prices stored inc-tax scenario)
    Config::set('lunar.pricing.stored_inclusive_of_tax', true);
    $priceInc = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'price' => 1200,   // stored inc 20 %
        'min_quantity' => 1,
    ]);
    expect($priceInc->priceExTax(taxZone: $zoneB)->value)->toEqual(1000);
});

test('priceIncTax accepts an explicit tax zone param', function () {
    Config::set('lunar.pricing.stored_inclusive_of_tax', false);

    $currency = Currency::factory()->create(['code' => 'AED', 'decimal_places' => 2, 'default' => true]);

    // Default zone: 0 % (global default for unknown locations)
    $defaultTaxZone = TaxZone::factory()->state(['default' => true])->create(['name' => 'Default']);
    $defaultTaxClass = TaxClass::factory()->create(['name' => 'Default', 'default' => true]);
    $defaultRate = TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])->create();
    TaxRateAmount::factory()->create([
        'tax_class_id' => $defaultTaxClass->id,
        'tax_rate_id' => $defaultRate->id,
        'percentage' => 0,
    ]);

    // UAE zone: 10 % VAT
    $uaeZone = TaxZone::factory()->state(['default' => false])->create(['name' => 'UAE']);
    $uae = Country::factory()->create(['iso3' => 'ARE', 'name' => 'United Arab Emirates']);
    TaxZoneCountry::factory()->create(['tax_zone_id' => $uaeZone->id, 'country_id' => $uae->id]);
    $uaeRate = TaxRate::factory()->state(['tax_zone_id' => $uaeZone])->create(['name' => 'UAE VAT']);
    $vatTaxClass = TaxClass::factory()->create(['name' => 'UAE VAT 10%']);
    TaxRateAmount::factory()->create([
        'tax_class_id' => $vatTaxClass->id,
        'tax_rate_id' => $uaeRate->id,
        'percentage' => 10,
    ]);

    $variant = ProductVariant::factory(['tax_class_id' => $vatTaxClass->id])->create();
    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'price' => 1000,
        'min_quantity' => 1,
    ]);

    // Without zone override – falls back to default zone → 0 % even though
    // the variant's class has UAE rates configured
    expect($price->priceIncTax()->value)->toEqual(1000);

    // Passing the UAE zone explicitly returns the 10 % rate for that zone
    expect($price->priceIncTax(taxZone: $uaeZone)->value)->toEqual(1100);
});

test('comparePriceIncTax accepts explicit taxZone param', function () {
    Config::set('lunar.pricing.stored_inclusive_of_tax', false);

    $currency = Currency::factory()->create(['code' => 'AED', 'decimal_places' => 2, 'default' => true]);

    $taxClass = TaxClass::factory()->create(['default' => true]);

    // Default zone: 0 %
    $defaultTaxZone = TaxZone::factory()->state(['default' => true])->create();
    $defaultRate = TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])->create();
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => $defaultRate->id,
        'percentage' => 0,
    ]);

    // UAE zone: 10 %
    $uaeZone = TaxZone::factory()->state(['default' => false])->create(['name' => 'UAE']);
    $uaeRate = TaxRate::factory()->state(['tax_zone_id' => $uaeZone])->create();
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => $uaeRate->id,
        'percentage' => 10,
    ]);

    $variant = ProductVariant::factory(['tax_class_id' => $taxClass->id])->create();
    $price = Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $variant->id,
        'priceable_type' => $variant->getMorphClass(),
        'price' => 1000,
        'compare_price' => 1500,
        'min_quantity' => 1,
    ]);

    // Default zone → 0 % on compare price
    expect($price->comparePriceIncTax()->value)->toEqual(1500);

    // UAE zone → 10 % on compare price
    expect($price->comparePriceIncTax(taxZone: $uaeZone)->value)->toEqual(1650);
});
