<?php

namespace Lunar\Shipping\Interfaces;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Country;

interface PostcodeResolverInterface
{
    /**
     * Whether this resolver supports the given country.
     */
    public function supportsCountry(Country $country): bool;

    /**
     * Return the postcode parts the resolver wants to match against zone records.
     *
     * @return Collection<int, string>
     */
    public function getParts(string $postcode, Country $country): Collection;
}
