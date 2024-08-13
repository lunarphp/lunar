<?php

use function Pest\Laravel\assertDatabaseHas;

uses(\Lunar\Tests\Stripe\Unit\TestCase::class);

it('can store payment intent address information', function () {
    $cart = \Lunar\Tests\Stripe\Utils\CartBuilder::build();

    $country = \Lunar\Models\Country::factory()->create([
        'iso2' => 'GB',
    ]);

    $order = $cart->createOrder();

    $paymentIntent = \Lunar\Stripe\Facades\Stripe::getClient()
        ->paymentIntents
        ->retrieve('PI_CAPTURE');

    app(\Lunar\Stripe\Actions\StoreAddressInformation::class)->store($order, $paymentIntent);

//    "address": {
//        "city": "ACME Shipping Land",
//          "country": "GB",
//          "line1": "123 ACME Shipping Lane",
//          "line2": null,
//          "postal_code": "AC2 2ME",
//          "state": "ACM3"
//      },
//      "email": "sales@acme.com",
//      "name": "Buggs Bunny"
//
    assertDatabaseHas(\Lunar\Models\OrderAddress::class, [
        'first_name' => 'Buggs',
        'last_name' => 'Bunny',
        'city' => 'ACME Shipping Land',
        'type' => 'shipping',
        'country_id' => $country->id,
        'line_one' => '123 ACME Shipping Lane',
        'postcode' => 'AC2 2ME',
        'state' => 'ACM3',
        'contact_phone' => '123456'
    ]);

    assertDatabaseHas(\Lunar\Models\OrderAddress::class, [
        'first_name' => 'Elma',
        'last_name' => 'Thudd',
        'city' => 'ACME Land',
        'type' => 'billing',
        'country_id' => $country->id,
        'line_one' => '123 ACME Lane',
        'postcode' => 'AC1 1ME',
        'state' => 'ACME',
        'contact_email' => 'sales@acme.com',
        'contact_phone' => '1234567'
    ]);
})->group('lunar.stripe.actions');
