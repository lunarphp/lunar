<?php

namespace Lunar\Shipping\DataTransferObjects;

use Lunar\Models\Contracts\Country as CountryContract;

class PostcodeLookup
{
    /**
     * Initialise the postcode lookup class.
     */
    public function __construct(
        public CountryContract $country,
        public string $postcode
    ) {
        //
    }
}
