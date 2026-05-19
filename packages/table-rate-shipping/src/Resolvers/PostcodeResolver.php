<?php

namespace Lunar\Shipping\Resolvers;

use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Country as CountryContract;
use Lunar\Shipping\Interfaces\PostcodeResolverInterface;

class PostcodeResolver implements PostcodeResolverInterface
{
    /**
     * ISO-2 country codes this resolver handles. An empty array matches every country,
     * making this resolver a safe catch-all when registered first.
     *
     * @var array<int, string>
     */
    protected array $countries = [];

    public function supportsCountry(CountryContract $country): bool
    {
        return empty($this->countries)
            || in_array($country->iso2, $this->countries, true);
    }

    public function getParts(string $postcode, CountryContract $country): Collection
    {
        $postcode = str_replace(' ', '', strtoupper($postcode));

        return collect([
            $postcode,
            rtrim(substr($postcode, 0, -3), 'a..zA..Z'),
            rtrim(substr($postcode, 0, -3), 'a..zA..Z').'*',
            rtrim($postcode, '0..9'),
            rtrim($postcode, '0..9').'*',
            substr($postcode, 0, 2),
            substr($postcode, 0, 2).'*',
            substr($postcode, 0, 1),
        ])->filter()->unique()->values();
    }
}
