<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\PickupPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => PickupPointsStub::unbind());

function routesCart(): Cart
{
    $cart = CheckoutCart::orderable();

    ShippingManifest::addOption(new ShippingOption(
        name: 'Click & collect',
        description: 'Collect from a branch',
        identifier: 'collection',
        price: new PriceValue(0, Currency::query()->firstWhere('default', true)),
        taxClass: TaxClass::query()->firstWhere('default', true),
        collect: true,
    ));

    return $cart->refresh();
}

it('projects delivery mode with the offered points and no selection by default', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London', ['SE20 8RA'])]);
    $session = CheckoutCart::session(routesCart());

    // X-Inertia makes show() return the prop payload as JSON, so the
    // projection is asserted without needing the built app manifest (see
    // CheckoutRouteTest's merchant-projection tests for the same pattern:
    // assertInertia() needs the Blade-rendered view, which needs the
    // published Vite manifest that isn't built in this test environment).
    $response = $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk();

    expect($response->json('props.checkout.fulfilment'))->toBe('delivery');
    expect($response->json('props.checkout.pickupPoints.0.id'))->toBe('london');
    expect($response->json('props.checkout.pickupPoints.0.lines.0'))->toBe('SE20 8RA');
    expect($response->json('props.checkout.pickupPointId'))->toBeNull();
    expect($response->json('props.checkout.urls.fulfilment'))->not->toBeNull();
    expect($response->json('props.checkout.urls.pickupPoint'))->not->toBeNull();
});

it('stores the mode and the point through their routes and re-projects them', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London'), new PickupPoint('dartford', 'Dartford')]);
    $session = CheckoutCart::session(routesCart());

    $this->post(route('lunar.checkout.fulfilment.store', $session->uuid), ['fulfilment' => 'collect'])
        ->assertRedirect();
    $this->post(route('lunar.checkout.pickup-point.store', $session->uuid), ['pickup_point' => 'dartford'])
        ->assertRedirect();

    $response = $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk();

    expect($response->json('props.checkout.fulfilment'))->toBe('collect');
    expect($response->json('props.checkout.shippingId'))->toBe('collection');
    expect($response->json('props.checkout.pickupPointId'))->toBe('dartford');
});

it('rejects an unknown point and an unknown mode', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London')]);
    $session = CheckoutCart::session(routesCart());

    $this->postJson(route('lunar.checkout.pickup-point.store', $session->uuid), ['pickup_point' => 'mars'])
        ->assertStatus(422)->assertJsonValidationErrors('pickup_point');
    $this->postJson(route('lunar.checkout.fulfilment.store', $session->uuid), ['fulfilment' => 'teleport'])
        ->assertStatus(422)->assertJsonValidationErrors('fulfilment');
});

it('hydrates collect mode on reload from the stored option alone', function () {
    $session = CheckoutCart::session(CheckoutCart::orderable(collect: true));

    $response = $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk();

    expect($response->json('props.checkout.fulfilment'))->toBe('collect');
});

it('returns the collect keys from the JSON start response', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London')]);
    routesCart();

    $this->postJson(route('lunar.checkout.start'))
        ->assertOk()
        ->assertJsonStructure(['uuid', 'fulfilment', 'pickupPoints', 'pickupPointId', 'urls' => ['fulfilment', 'pickupPoint']]);
});
