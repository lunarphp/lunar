<?php

namespace Lunar\Checkout\DataObjects;

use Spatie\LaravelData\Data;

/**
 * Backend-neutral address shape crossing the driver boundary (spec 0010 §B).
 */
class CheckoutAddress extends Data
{
    public function __construct(
        public string $countryCode,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $companyName = null,
        public ?string $line1 = null,
        public ?string $line2 = null,
        public ?string $line3 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postcode = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}
}
