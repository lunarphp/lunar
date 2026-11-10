<?php

use Lunar\Models\Country;
use Lunar\Models\OrderAddress;
use Lunar\Stripe\Actions\StoreAddressInformation;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;

use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class);

it('can store payment intent address information', function () {
    $cart = CartBuilder::build();

    $country = Country::firstOrCreate(
        ['iso2' => 'GB'],
        Country::factory()->raw(['iso2' => 'GB']),
    );

    $order = $cart->createOrder();

    $paymentIntent = Stripe::getClient()
        ->paymentIntents
        ->retrieve('PI_CAPTURE');

    app(StoreAddressInformation::class)->store($order, $paymentIntent);

    assertDatabaseHas(OrderAddress::class, [
        'first_name' => 'Buggs Bunny',
        'last_name' => null,
        'city' => 'ACME Shipping Land',
        'type' => 'shipping',
        'country_id' => $country->id,
        'line_one' => '123 ACME Shipping Lane',
        'postcode' => 'AC2 2ME',
        'state' => 'ACM3',
        'contact_phone' => '123456',
    ]);

    assertDatabaseHas(OrderAddress::class, [
        'first_name' => 'Elma Thudd',
        'last_name' => null,
        'city' => 'ACME Land',
        'type' => 'billing',
        'country_id' => $country->id,
        'line_one' => '123 ACME Lane',
        'postcode' => 'AC1 1ME',
        'state' => 'ACME',
        'contact_email' => 'sales@acme.com',
        'contact_phone' => '1234567',
    ]);
})->group('lunar.stripe.actions');

it('can store link payment intent address information', function () {
    $cart = CartBuilder::build();

    $country = Country::firstOrCreate(
        ['iso2' => 'GB'],
        Country::factory()->raw(['iso2' => 'GB']),
    );

    $order = $cart->createOrder();

    $paymentIntent = Stripe::getClient()
        ->paymentIntents
        ->retrieve('PI_CAPTURE_LINK');

    app(StoreAddressInformation::class)->store($order, $paymentIntent);

    assertDatabaseHas(OrderAddress::class, [
        'first_name' => 'Buggs Bunny',
        'last_name' => null,
        'city' => 'ACME Shipping Land',
        'type' => 'shipping',
        'country_id' => $country->id,
        'line_one' => '123 ACME Shipping Lane',
        'postcode' => 'AC2 2ME',
        'state' => 'ACM3',
        'contact_phone' => '123456',
    ]);

    assertDatabaseHas(OrderAddress::class, [
        'first_name' => null,
        'last_name' => null,
        'city' => '',
        'type' => 'billing',
        'country_id' => $country->id,
        'line_one' => '',
        'postcode' => '',
        'state' => '',
        'contact_email' => 'sales@acme.com',
        'contact_phone' => null,
    ]);
})->group('lunar.stripe.actions');
