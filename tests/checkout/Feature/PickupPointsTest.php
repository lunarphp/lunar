<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Checkout\Contracts\LocatesCustomer;
use Lunar\Checkout\Contracts\PickupPointProvider;
use Lunar\Checkout\DataTypes\Coordinates;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Checkout\Support\PickupPoints;
use Lunar\Core\Models\Cart;
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
        'location' => null,
    ]);

    expect(PickupPoint::fromArray($point->toArray()))->toEqual($point);
});

it('round-trips a located pickup point and reads a snapshot stored before locations existed', function () {
    $point = new PickupPoint('dartford', 'Dartford', location: new Coordinates(51.4425, 0.2301));

    expect($point->toArray()['location'])->toBe(['latitude' => 51.4425, 'longitude' => 0.2301])
        ->and(PickupPoint::fromArray($point->toArray()))->toEqual($point)
        ->and(PickupPoint::fromArray(['handle' => 'old', 'name' => 'Old'])->location)->toBeNull();
});

it('projects point locations and the host origin when the provider can locate the customer', function () {
    app()->instance(PickupPointProvider::class, new class implements LocatesCustomer, PickupPointProvider
    {
        public function pointsFor(Cart $cart): Collection
        {
            return collect([
                new PickupPoint('london', 'London', location: new Coordinates(51.4131, -0.0554)),
                new PickupPoint('dartford', 'Dartford'),
            ]);
        }

        public function originFor(Cart $cart): ?Coordinates
        {
            return new Coordinates(51.5, -0.1);
        }
    });

    $cart = CheckoutCart::orderable(collect: true);
    $session = CheckoutCart::session($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.pickupPoints.0.location', ['latitude' => 51.4131, 'longitude' => -0.0554])
        ->assertJsonPath('props.checkout.pickupPoints.1.location', null)
        ->assertJsonPath('props.checkout.pickupOrigin', ['latitude' => 51.5, 'longitude' => -0.1])
        ->assertJsonPath('props.checkout.distanceUnit', null);
});

it('projects no origin when the provider only lists points', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London'), new PickupPoint('dartford', 'Dartford')]);
    $cart = CheckoutCart::orderable(collect: true);
    $session = CheckoutCart::session($cart);

    config()->set('lunar.checkout.pickup.distance_unit', 'km');

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.pickupOrigin', null)
        ->assertJsonPath('props.checkout.distanceUnit', 'km');
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
