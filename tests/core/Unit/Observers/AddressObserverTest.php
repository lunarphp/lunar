<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\Address;
use Lunar\Models\Customer;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can only have one shipping default per customer', function () {
    $customer = Customer::factory()->create();

    $addressA = Address::factory()->create([
        'customer_id' => $customer->id,
        'shipping_default' => true,
    ]);

    expect($addressA->shipping_default)->toBeTrue();

    $addressB = Address::factory()->create([
        'customer_id' => $customer->id,
        'shipping_default' => true,
    ]);

    expect($addressA->refresh()->shipping_default)->toBeFalse();
    expect($addressB->shipping_default)->toBeTrue();

    $addressA->update([
        'shipping_default' => true,
    ]);

    expect($addressA->shipping_default)->toBeTrue();
    expect($addressB->refresh()->shipping_default)->toBeFalse();
});

test('can only have one billing default per customer', function () {
    $customer = Customer::factory()->create();

    $addressA = Address::factory()->create([
        'customer_id' => $customer->id,
        'billing_default' => true,
    ]);

    expect($addressA->billing_default)->toBeTrue();

    $addressB = Address::factory()->create([
        'customer_id' => $customer->id,
        'billing_default' => true,
    ]);

    expect($addressA->refresh()->billing_default)->toBeFalse();
    expect($addressB->billing_default)->toBeTrue();

    $addressA->update([
        'billing_default' => true,
    ]);

    expect($addressA->billing_default)->toBeTrue();
    expect($addressB->refresh()->billing_default)->toBeFalse();
});
