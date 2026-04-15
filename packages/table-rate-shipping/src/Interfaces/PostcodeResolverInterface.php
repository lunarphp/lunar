<?php

namespace Lunar\Shipping\Interfaces;

use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Country as CountryContract;

interface PostcodeResolverInterface
{
    /**
     * Whether this resolver supports the given country.
     */
    public function supportsCountry(CountryContract $country): bool;

    /**
     * Return the postcode parts the resolver wants to match against zone records.
     *
     * @return Collection<int, string>
     */
    public function getParts(string $postcode, CountryContract $country): Collection;
}
