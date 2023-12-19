<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Price as PriceModel;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $this->cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

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

    PriceModel::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $this->cart->lines()->create([
        'purchasable_type' => get_class($purchasable),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);
});

test('can add option', function () {
    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(0);

    $taxClass = TaxClass::factory()->create();

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        )
    );

    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(1);
});

test('can add multiple options', function () {
    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(0);

    $taxClass = TaxClass::factory()->create();

    ShippingManifest::addOption(
        $basicOption = new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        )
    );

    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(1);

    $options = collect();

    for ($i = 1; $i <= 5; $i++) {
        $options->push(new ShippingOption(
            name: 'Basic Delivery #'.$i,
            description: 'Basic Delivery',
            identifier: 'BASDEL'.$i,
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        ));
    }

    ShippingManifest::addOptions($options);

    $manifestOptions = ShippingManifest::getOptions($this->cart);
    expect($manifestOptions)->toHaveCount(6);

    expect($manifestOptions->first())->toBe($basicOption);
    expect($manifestOptions->last())->toBe($options->last());
});

test('cannot add the same option identifier more than once', function () {
    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(0);

    $taxClass = TaxClass::factory()->create();

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        )
    );

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        )
    );

    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(1);
});

test('can clear options', function () {
    $taxClass = TaxClass::factory()->create();

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        )
    );

    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(1);

    ShippingManifest::clearOptions();

    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(0);
});

test('can retrieve option', function () {
    $taxClass = TaxClass::factory()->create();

    ShippingManifest::addOption(
        $option1 = new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        )
    );

    ShippingManifest::addOption(
        $option2 = new ShippingOption(
            name: 'Basic Delivery 2',
            description: 'Basic Delivery',
            identifier: 'BASDEL2',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        )
    );

    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(2);

    expect(ShippingManifest::getOption($this->cart, $option1->getIdentifier()))->toBe($option1);
});

test('can retrieve cart shipping option', function () {
    $taxClass = TaxClass::factory()->create();

    ShippingManifest::addOption(
        $option = new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        )
    );

    $shipping = CartAddress::factory()->make([
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    $this->cart->setShippingAddress($shipping);

    expect(ShippingManifest::getShippingOption($this->cart))->toBeNull();

    $this->cart->setShippingOption($option);

    expect(ShippingManifest::getShippingOption($this->cart))->toBe($option);
});

test('can retrieve cart shipping option using', function () {
    $taxClass = TaxClass::factory()->create();

    $option = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new Price(500, $this->cart->currency, 1),
        taxClass: $taxClass
    );

    expect(ShippingManifest::getOptions($this->cart))->toHaveCount(0);

    $shipping = CartAddress::factory()->make([
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    $this->cart->setShippingAddress($shipping);

    $this->cart->setShippingOption($option);

    expect(ShippingManifest::getShippingOption($this->cart))->toBeNull();

    ShippingManifest::getOptionUsing(fn (Cart $cart, $identifier): ShippingOption => $option->getIdentifier() == $identifier ? $option : null);

    expect(ShippingManifest::getShippingOption($this->cart))->toBe($option);
});
