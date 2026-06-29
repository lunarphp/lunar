<?php

namespace Lunar\Shipping\DataTransferObjects;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Country;
use Lunar\Shipping\Facades\Postcode;

class PostcodeLookup
{
    public function __construct(
        public Country $country,
        public string $postcode
    ) {
        //
    }

    /**
     * Return the postcode parts for this lookup, delegating to the country-matched resolver.
     *
     * @return Collection<int, string>
     */
    public function getParts(): Collection
    {
        return Postcode::country($this->country)->getParts($this->postcode, $this->country);
    }
}
