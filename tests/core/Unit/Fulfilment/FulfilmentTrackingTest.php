<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\CarrierManifest;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Shipping\GenericCarrier;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
    Location::factory()->default()->create();

    app()->forgetInstance(CarrierManifest::class);
    Carriers::register(new GenericCarrier(
        key: 'acme',
        name: 'ACME',
        trackingUrl: 'https://acme.test/track/{tracking_number}',
        services: ['Next Day'],
        trackingNumberPattern: '/^[A-Z]{2}\d{6}$/',
    ));

    $this->order = Order::factory()->create();
    $this->line = OrderLine::factory()->create([
        'order_id' => $this->order->id,
        'type' => 'physical',
        'quantity' => 3,
    ]);
});

it('derives the tracking url from the carrier when none is stored', function () {
    $fulfilment = Fulfilments::ship(
        Fulfilments::create($this->order, [$this->line->id => 1]),
        [['carrier' => 'acme', 'tracking_number' => 'AB123456', 'shipping_method' => 'Next Day']],
    );

    $tracking = $fulfilment->trackings()->first();

    expect($tracking->carrier)->toBe('acme')
        ->and($tracking->carrier())->not->toBeNull()
        ->and($tracking->url)->toBe('https://acme.test/track/AB123456');
});

it('prefers a stored tracking url over the derived one', function () {
    $fulfilment = Fulfilments::ship(Fulfilments::create($this->order, [$this->line->id => 1]));

    $tracking = Fulfilments::addTracking($fulfilment, [
        'carrier' => 'acme',
        'tracking_number' => 'AB123456',
        'tracking_url' => 'https://custom.test/override',
    ]);

    expect($tracking->url)->toBe('https://custom.test/override');
});

it('rejects a tracking number that does not match the carrier format', function () {
    $fulfilment = Fulfilments::ship(Fulfilments::create($this->order, [$this->line->id => 1]));

    Fulfilments::addTracking($fulfilment, ['carrier' => 'acme', 'tracking_number' => 'nope']);
})->throws(FulfilmentException::class);

it('rejects an invalid carrier tracking number at ship time', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 1]);

    Fulfilments::ship($fulfilment, [['carrier' => 'acme', 'tracking_number' => 'nope']]);
})->throws(FulfilmentException::class);
