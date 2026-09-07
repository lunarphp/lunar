<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Checkout\Support\PickupPoints;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\PickupPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => PickupPointsStub::unbind());

it('round-trips a pickup point through an array', function () {
    $point = new PickupPoint('dartford', 'Dartford', ['Unit 4', 'DA2 6EP'], ['phone' => '01322 000000']);

    expect($point->toArray())->toBe([
        'handle' => 'dartford',
        'name' => 'Dartford',
        'lines' => ['Unit 4', 'DA2 6EP'],
        'meta' => ['phone' => '01322 000000'],
    ]);

    expect(PickupPoint::fromArray($point->toArray()))->toEqual($point);
});

it('offers nothing when no provider is bound', function () {
    $cart = CheckoutCart::orderable(collect: true);

    expect(PickupPoints::offered($cart))->toBeEmpty()
        ->and(PickupPoints::missing($cart))->toBeFalse();
});

it('offers the provider points and finds one by handle', function () {
    PickupPointsStub::bind([
        new PickupPoint('london', 'London'),
        new PickupPoint('dartford', 'Dartford'),
    ]);
    $cart = CheckoutCart::orderable(collect: true);

    expect(PickupPoints::offered($cart))->toHaveCount(2)
        ->and(PickupPoints::find($cart, 'dartford')?->name)->toBe('Dartford')
        ->and(PickupPoints::find($cart, 'nowhere'))->toBeNull();
});

it('derives the fulfilment mode from the stored option when the meta key is absent', function () {
    $collect = CheckoutCart::orderable(collect: true);
    expect(PickupPoints::fulfilment($collect))->toBe('collect');

    $collect->meta = ['fulfilment' => 'delivery'];
    $collect->save();
    expect(PickupPoints::fulfilment($collect->refresh()))->toBe('delivery');
});

it('does not report a missing point for a delivery cart', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London'), new PickupPoint('dartford', 'Dartford')]);

    $delivery = CheckoutCart::orderable();
    expect(PickupPoints::missing($delivery))->toBeFalse();
});

it('reports a missing point only when collecting with points on offer and none chosen', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London'), new PickupPoint('dartford', 'Dartford')]);

    $collect = CheckoutCart::orderable(collect: true);
    expect(PickupPoints::missing($collect))->toBeTrue();

    $collect->meta = ['pickup_point' => ['handle' => 'dartford', 'name' => 'Dartford', 'lines' => [], 'meta' => []]];
    $collect->save();
    expect(PickupPoints::missing($collect->refresh()))->toBeFalse();

    $collect->meta = ['pickup_point' => ['handle' => 'closed', 'name' => 'Closed', 'lines' => [], 'meta' => []]];
    $collect->save();
    expect(PickupPoints::missing($collect->refresh()))->toBeTrue()
        ->and(PickupPoints::chosenHandle($collect))->toBeNull();
});
