<?php

namespace Lunar\Tests\Shipping\Stubs\Resolvers;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Country;
use Lunar\Shipping\Interfaces\PostcodeResolverInterface;

class TestCustomPostcodeResolver implements PostcodeResolverInterface
{
    /**
     * ISO-2 codes this test resolver claims. Override via subclass if you need a different set.
     *
     * @var array<int, string>
     */
    protected array $countries = [];

    public function supportsCountry(Country $country): bool
    {
        return empty($this->countries)
            || in_array($country->iso2, $this->countries, true);
    }

    public function getParts(string $postcode, Country $country): Collection
    {
        $postcode = str_replace(' ', '', strtoupper($postcode));

        return collect([
            $postcode,
            substr($postcode, 0, 1).'*',
            substr($postcode, 0, 2).'*',
            substr($postcode, 0, 3).'*',
            substr($postcode, 0, 4).'*',
        ])->filter()->unique()->values();
    }
}
