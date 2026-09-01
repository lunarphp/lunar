<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lunar\Checkout\AddressLookup\IdealPostcodes;
use Lunar\Checkout\Contracts\AddressLookup;
use Lunar\Checkout\Exceptions\AddressLookupException;
use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('lunar.checkout.address_lookup.driver', 'ideal_postcodes');
    config()->set('lunar.checkout.address_lookup.ideal_postcodes.key', 'test-key');
    Cache::flush();
});

function idealPostcodesBody(array $result): array
{
    return ['code' => 2000, 'message' => 'Success', 'result' => $result];
}

it('is selected by config and reports itself available when a key is set', function () {
    expect(app(AddressLookup::class))->toBeInstanceOf(IdealPostcodes::class)
        ->and(app(AddressLookup::class)->isAvailable())->toBeTrue();
});

it('is unavailable without an api key', function () {
    config()->set('lunar.checkout.address_lookup.ideal_postcodes.key', null);

    expect(app(AddressLookup::class)->isAvailable())->toBeFalse();
});

it('maps a vendor hit onto CheckoutAddress', function () {
    Http::fake(['api.ideal-postcodes.co.uk/*' => Http::response(idealPostcodesBody([
        [
            'line_1' => '10 Downing Street',
            'line_2' => 'Westminster',
            'line_3' => '',
            'post_town' => 'LONDON',
            'county' => 'Greater London',
            'postcode' => 'SW1A 2AA',
        ],
    ]))]);

    $addresses = app(AddressLookup::class)->lookup('sw1a2aa');

    expect($addresses)->toHaveCount(1);
    expect($addresses[0]->line1)->toBe('10 Downing Street')
        ->and($addresses[0]->line2)->toBe('Westminster')
        ->and($addresses[0]->line3)->toBeNull()
        ->and($addresses[0]->city)->toBe('LONDON')
        ->and($addresses[0]->state)->toBe('Greater London')
        ->and($addresses[0]->postcode)->toBe('SW1A 2AA')
        ->and($addresses[0]->countryCode)->toBe('GB');
});

it('treats an empty result as an empty list, not an error', function () {
    Http::fake(['api.ideal-postcodes.co.uk/*' => Http::response(idealPostcodesBody([]))]);

    expect(app(AddressLookup::class)->lookup('SW1A 2AA'))->toBe([]);
});

it('raises AddressLookupException on a vendor server error', function () {
    Http::fake(['api.ideal-postcodes.co.uk/*' => Http::response('upstream exploded', 500)]);

    app(AddressLookup::class)->lookup('SW1A 2AA');
})->throws(AddressLookupException::class);

it('raises AddressLookupException on a vendor client error', function () {
    Http::fake(['api.ideal-postcodes.co.uk/*' => Http::response(['code' => 4010], 401)]);

    app(AddressLookup::class)->lookup('SW1A 2AA');
})->throws(AddressLookupException::class);

it('serves a repeat lookup from cache without billing a second request', function () {
    Http::fake(['api.ideal-postcodes.co.uk/*' => Http::response(idealPostcodesBody([
        [
            'line_1' => '10 Downing Street',
            'line_2' => '',
            'line_3' => '',
            'post_town' => 'LONDON',
            'county' => '',
            'postcode' => 'SW1A 2AA',
        ],
    ]))]);

    $driver = app(AddressLookup::class);

    $driver->lookup('SW1A 2AA');
    $driver->lookup('sw1a  2aa');

    Http::assertSentCount(1);
});
