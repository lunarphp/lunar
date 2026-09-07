<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\DataTypes\CollectionPoint;
use Lunar\Checkout\Support\CollectionPoints;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\CollectionPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => CollectionPointsStub::unbind());

it('round-trips a collection point through an array', function () {
    $point = new CollectionPoint('dartford', 'Dartford', ['Unit 4', 'DA2 6EP'], ['phone' => '01322 000000']);

    expect($point->toArray())->toBe([
        'handle' => 'dartford',
        'name' => 'Dartford',
        'lines' => ['Unit 4', 'DA2 6EP'],
        'meta' => ['phone' => '01322 000000'],
    ]);

    expect(CollectionPoint::fromArray($point->toArray()))->toEqual($point);
});

it('offers nothing when no provider is bound', function () {
    $cart = CheckoutCart::orderable(collect: true);

    expect(CollectionPoints::offered($cart))->toBeEmpty()
        ->and(CollectionPoints::missing($cart))->toBeFalse();
});

it('offers the provider points and finds one by handle', function () {
    CollectionPointsStub::bind([
        new CollectionPoint('london', 'London'),
        new CollectionPoint('dartford', 'Dartford'),
    ]);
    $cart = CheckoutCart::orderable(collect: true);

    expect(CollectionPoints::offered($cart))->toHaveCount(2)
        ->and(CollectionPoints::find($cart, 'dartford')?->name)->toBe('Dartford')
        ->and(CollectionPoints::find($cart, 'nowhere'))->toBeNull();
});

it('derives the fulfilment mode from the stored option when the meta key is absent', function () {
    $collect = CheckoutCart::orderable(collect: true);
    expect(CollectionPoints::fulfilment($collect))->toBe('collect');

    $collect->meta = ['fulfilment' => 'delivery'];
    $collect->save();
    expect(CollectionPoints::fulfilment($collect->refresh()))->toBe('delivery');
});

it('does not report a missing point for a delivery cart', function () {
    CollectionPointsStub::bind([new CollectionPoint('london', 'London'), new CollectionPoint('dartford', 'Dartford')]);

    $delivery = CheckoutCart::orderable();
    expect(CollectionPoints::missing($delivery))->toBeFalse();
});

it('reports a missing point only when collecting with points on offer and none chosen', function () {
    CollectionPointsStub::bind([new CollectionPoint('london', 'London'), new CollectionPoint('dartford', 'Dartford')]);

    $collect = CheckoutCart::orderable(collect: true);
    expect(CollectionPoints::missing($collect))->toBeTrue();

    $collect->meta = ['collection_point' => ['handle' => 'dartford', 'name' => 'Dartford', 'lines' => [], 'meta' => []]];
    $collect->save();
    expect(CollectionPoints::missing($collect->refresh()))->toBeFalse();

    $collect->meta = ['collection_point' => ['handle' => 'closed', 'name' => 'Closed', 'lines' => [], 'meta' => []]];
    $collect->save();
    expect(CollectionPoints::missing($collect->refresh()))->toBeTrue()
        ->and(CollectionPoints::chosenHandle($collect))->toBeNull();
});
