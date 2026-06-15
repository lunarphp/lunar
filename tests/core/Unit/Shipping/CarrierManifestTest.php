<?php

use Lunar\Core\Contracts\ShippingCarrier;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Shipping\Carriers\Carrier;
use Lunar\Core\Shipping\Carriers\RoyalMail;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('shipping.carriers');

/**
 * A configurable carrier double for exercising the shared base mechanics.
 */
function testCarrier(?string $trackingUrl = null, ?string $pattern = null): ShippingCarrier
{
    return new class($trackingUrl, $pattern) extends Carrier
    {
        public function __construct(
            protected ?string $trackingUrl,
            protected ?string $pattern,
        ) {}

        public function getKey(): string
        {
            return 'acme';
        }

        public function getName(): string
        {
            return 'ACME Couriers';
        }

        public function getServices(): array
        {
            return ['Next Day', 'Economy'];
        }

        protected function trackingUrlTemplate(): ?string
        {
            return $this->trackingUrl;
        }

        protected function trackingNumberPattern(): ?string
        {
            return $this->pattern;
        }
    };
}

it('registers the batteries-included core carriers', function () {
    $carrier = Carriers::get('royal-mail');

    expect($carrier)->toBeInstanceOf(RoyalMail::class)
        ->and($carrier->getName())->toBe('Royal Mail')
        ->and($carrier->getServices())->toContain('Tracked 24')
        ->and(Carriers::all()->keys()->all())->toContain('dpd', 'ups', 'fedex');
});

it('builds a tracking url from the template', function () {
    $carrier = testCarrier(trackingUrl: 'https://acme.test/track/{tracking_number}');

    expect($carrier->getTrackingUrl('AB 12'))->toBe('https://acme.test/track/AB%2012');
});

it('returns null tracking url when no template is set', function () {
    expect(testCarrier()->getTrackingUrl('ABC123'))->toBeNull();
});

it('validates tracking numbers against an optional pattern', function () {
    $carrier = testCarrier(pattern: '/^[A-Z]{2}\d{6}$/');

    expect($carrier->validateTrackingNumber('AB123456'))->toBeTrue()
        ->and($carrier->validateTrackingNumber('nope'))->toBeFalse();
});

it('always passes validation without a pattern', function () {
    expect(testCarrier()->validateTrackingNumber('anything'))->toBeTrue();
});

it('registers a custom carrier instance', function () {
    Carriers::register(testCarrier());

    expect(Carriers::get('acme')?->getName())->toBe('ACME Couriers')
        ->and(Carriers::get(null))->toBeNull();
});
