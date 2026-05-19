<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Country as CountryContract;
use Lunar\Models\Country;
use Lunar\Shipping\DataTransferObjects\PostcodeLookup;
use Lunar\Shipping\Facades\Postcode;
use Lunar\Shipping\Interfaces\PostcodeResolverInterface;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class)
    ->group('shipping', 'shipping-postcode');

uses(RefreshDatabase::class);

test('getParts delegates to the resolver matched for the lookup country', function () {
    $country = Country::factory()->create(['iso2' => 'GB']);

    $stubbed = new class implements PostcodeResolverInterface
    {
        public function supportsCountry(CountryContract $country): bool
        {
            return $country->iso2 === 'GB';
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect([sprintf('STUB:%s:%s', $country->iso2, $postcode)]);
        }
    };

    Postcode::addResolver($stubbed);

    $lookup = new PostcodeLookup($country, 'SW1A 1AA');

    expect($lookup->getParts()->all())->toBe(['STUB:GB:SW1A 1AA']);
});
