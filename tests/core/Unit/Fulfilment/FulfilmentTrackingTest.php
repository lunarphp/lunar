<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Shipping\Carriers\Carrier;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
    Location::factory()->default()->create();

    Carriers::register(new class extends Carrier
    {
        public function getKey(): string
        {
            return 'acme';
        }

        public function getName(): string
        {
            return 'ACME';
        }

        public function getServices(): array
        {
            return ['Next Day'];
        }

        protected function trackingUrlTemplate(): ?string
        {
            return 'https://acme.test/track/{tracking_number}';
        }

        protected function trackingNumberPattern(): ?string
        {
            return '/^[A-Z]{2}\d{6}$/';
        }
    });

    $this->order = Order::factory()->create();
    $this->line = OrderLine::factory()->create([
        'order_id' => $this->order->id,
        'type' => 'physical',
        'quantity' => 3,
    ]);
});

it('derives the tracking url from the carrier when none is stored', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 1])->ship([['carrier' => 'acme', 'tracking_number' => 'AB123456', 'shipping_method' => 'Next Day']],
    );

    $tracking = $fulfilment->trackings()->first();

    expect($tracking->carrier)->toBe('acme')
        ->and($tracking->carrier())->not->toBeNull()
        ->and($tracking->url)->toBe('https://acme.test/track/AB123456');
});

it('prefers a stored tracking url over the derived one', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 1])->ship();

    $tracking = $fulfilment->addTracking([
        'carrier' => 'acme',
        'tracking_number' => 'AB123456',
        'tracking_url' => 'https://custom.test/override',
    ]);

    expect($tracking->url)->toBe('https://custom.test/override');
});

it('rejects a tracking number that does not match the carrier format', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 1])->ship();

    $fulfilment->addTracking(['carrier' => 'acme', 'tracking_number' => 'nope']);
})->throws(FulfilmentException::class);

it('rejects an invalid carrier tracking number at ship time', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 1]);

    $fulfilment->ship([['carrier' => 'acme', 'tracking_number' => 'nope']]);
})->throws(FulfilmentException::class);

it('removes a tracking reference', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 1])->ship([
        ['tracking_number' => 'TRK-1'],
        ['tracking_number' => 'TRK-2'],
    ]);

    $tracking = $fulfilment->trackings()->first();

    $tracking->remove();

    expect($fulfilment->refresh()->trackings)->toHaveCount(1)
        ->and($fulfilment->trackings->pluck('tracking_number'))->not->toContain('TRK-1');
});
