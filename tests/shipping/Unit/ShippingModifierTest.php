<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\TaxClass;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class)->group('shipping', 'shipping-modifier');
uses(RefreshDatabase::class);
uses(TestUtils::class);

test('can set correct shipping options', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $country = Country::factory()->create();

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingZone->countries()->attach($country);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'code' => 'BASEDEL',
        'data' => [
            'minimum_spend' => [
                "{$currency->code}" => 200,
            ],
        ],
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);
    $shippingMethod->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        [
            'price' => 1000,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 0,
            'min_quantity' => 500,
            'currency_id' => $currency->id,
        ],
    ]);

    $cart = $this->createCart($currency, 6000, calculate: false);

    $cart->shippingAddress()->create(
        CartAddress::factory()->make([
            'country_id' => $country->id,
            'shipping_option' => 'BASEDEL',
            'state' => null,
            'type' => 'shipping',
        ])->toArray()
    );

    $option = $cart->refresh()->getShippingOption();

    expect($option->price->value)->toBe(0);
});

test('drops table-rate options the previous resolution offered but this one does not, and keeps foreign options', function () {
    $currency = Currency::factory()->create(['default' => true]);
    $country = Country::factory()->create();
    $elsewhere = Country::factory()->create();
    $taxClass = TaxClass::factory()->create(['default' => true]);

    $zone = ShippingZone::factory()->create(['type' => 'countries']);
    $zone->countries()->attach($country);

    $method = ShippingMethod::factory()->create(['driver' => 'ship-by', 'code' => 'BASEDEL', 'data' => ['minimum_spend' => [$currency->code => 200]]]);
    $method->customerGroups()->sync([
        CustomerGroup::factory()->create(['default' => true])->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
    ]);
    $rate = ShippingRate::factory()->create(['shipping_method_id' => $method->id, 'shipping_zone_id' => $zone->id]);
    $rate->prices()->create(['price' => 1000, 'min_quantity' => 1, 'currency_id' => $currency->id]);

    $foreign = new ShippingOption(
        name: 'Courier from elsewhere',
        description: '',
        identifier: 'foreign-courier',
        price: new PriceValue(500, $currency),
        taxClass: $taxClass,
    );
    ShippingManifest::addOption($foreign);

    $inZone = $this->createCart($currency, 6000, calculate: false);
    $inZone->shippingAddress()->create(CartAddress::factory()->make(['country_id' => $country->id, 'state' => null, 'type' => 'shipping'])->toArray());

    expect(ShippingManifest::getOptions($inZone->refresh())->pluck('identifier')->all())
        ->toEqualCanonicalizing(['foreign-courier', 'BASEDEL']);

    $outOfZone = $this->createCart($currency, 6000, calculate: false);
    $outOfZone->shippingAddress()->create(CartAddress::factory()->make(['country_id' => $elsewhere->id, 'state' => null, 'type' => 'shipping'])->toArray());

    expect(ShippingManifest::getOptions($outOfZone->refresh())->pluck('identifier')->all())
        ->toBe(['foreign-courier']);
});

test('the latest resolution prices a method that resolves in different zones', function () {
    $currency = Currency::factory()->create(['default' => true]);
    $near = Country::factory()->create();
    $far = Country::factory()->create();
    TaxClass::factory()->create(['default' => true]);

    $method = ShippingMethod::factory()->create(['driver' => 'ship-by', 'code' => 'BASEDEL', 'data' => ['minimum_spend' => [$currency->code => 200]]]);
    $method->customerGroups()->sync([
        CustomerGroup::factory()->create(['default' => true])->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
    ]);

    foreach ([[$near, 1000], [$far, 2500]] as [$country, $price]) {
        $zone = ShippingZone::factory()->create(['type' => 'countries']);
        $zone->countries()->attach($country);
        $rate = ShippingRate::factory()->create(['shipping_method_id' => $method->id, 'shipping_zone_id' => $zone->id]);
        $rate->prices()->create(['price' => $price, 'min_quantity' => 1, 'currency_id' => $currency->id]);
    }

    $nearCart = $this->createCart($currency, 6000, calculate: false);
    $nearCart->shippingAddress()->create(CartAddress::factory()->make(['country_id' => $near->id, 'state' => null, 'type' => 'shipping'])->toArray());

    expect(ShippingManifest::getOptions($nearCart->refresh())->firstWhere('identifier', 'BASEDEL')->price->value)
        ->toBe(1000);

    $farCart = $this->createCart($currency, 6000, calculate: false);
    $farCart->shippingAddress()->create(CartAddress::factory()->make(['country_id' => $far->id, 'state' => null, 'type' => 'shipping'])->toArray());

    $options = ShippingManifest::getOptions($farCart->refresh());

    expect($options->pluck('identifier')->all())->toBe(['BASEDEL']);
    expect($options->first()->price->value)->toBe(2500);
});
