<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Country;
use Lunar\Shipping\Resolvers\PostcodeResolver;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class)
    ->group('shipping', 'shipping-postcode');
uses(RefreshDatabase::class);

test('splits a UK postcode into queryable parts', function () {
    $country = Country::factory()->create(['iso2' => 'GB']);

    $parts = (new PostcodeResolver)->getParts('SW1A 1AA', $country);

    expect($parts)->toContain('SW1A1AA');
    expect($parts)->toContain('SW1');
    expect($parts)->toContain('SW');
    expect($parts)->toContain('S');
});

test('supportsCountry returns true for every country when the countries array is empty', function () {
    $gb = Country::factory()->create(['iso2' => 'GB']);
    $us = Country::factory()->create(['iso2' => 'US']);

    $resolver = new PostcodeResolver;

    expect($resolver->supportsCountry($gb))->toBeTrue();
    expect($resolver->supportsCountry($us))->toBeTrue();
});

test('supportsCountry restricts to listed iso2 codes when the property is set on a subclass', function () {
    $gb = Country::factory()->create(['iso2' => 'GB']);
    $us = Country::factory()->create(['iso2' => 'US']);

    $resolver = new class extends PostcodeResolver
    {
        protected array $countries = ['GB'];
    };

    expect($resolver->supportsCountry($gb))->toBeTrue();
    expect($resolver->supportsCountry($us))->toBeFalse();
});
