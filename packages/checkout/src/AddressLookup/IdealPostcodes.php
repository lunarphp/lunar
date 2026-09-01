<?php

namespace Lunar\Checkout\AddressLookup;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as Http;
use Lunar\Checkout\Contracts\AddressLookup;
use Lunar\Checkout\DataObjects\CheckoutAddress;
use Lunar\Checkout\Exceptions\AddressLookupException;

/**
 * UK postcode lookup against Ideal Postcodes (spec 0011 §B).
 *
 * Results are cached on the normalised postcode: postcodes are static data and
 * the vendor bills per lookup, so the same search must not be charged twice.
 */
class IdealPostcodes implements AddressLookup
{
    private const ENDPOINT = 'https://api.ideal-postcodes.co.uk/v1/postcodes/';

    public function __construct(
        private readonly Http $http,
        private readonly Cache $cache,
    ) {}

    public function lookup(string $postcode): array
    {
        $normalised = $this->normalise($postcode);

        if ($normalised === '' || ! $this->isAvailable()) {
            return [];
        }

        /** @var array<int, array<string, string|null>> $rows */
        $rows = $this->cache->remember(
            'lunar.checkout.address-lookup.ideal-postcodes.'.$normalised,
            (int) config('lunar.checkout.address_lookup.ideal_postcodes.cache_ttl', 2592000),
            fn (): array => $this->fetch($normalised),
        );

        return array_map(fn (array $row): CheckoutAddress => new CheckoutAddress(
            countryCode: 'GB',
            line1: $this->value($row, 'line_1'),
            line2: $this->value($row, 'line_2'),
            line3: $this->value($row, 'line_3'),
            city: $this->value($row, 'post_town'),
            state: $this->value($row, 'county'),
            postcode: $this->value($row, 'postcode'),
        ), $rows);
    }

    public function isAvailable(): bool
    {
        return filled(config('lunar.checkout.address_lookup.ideal_postcodes.key'));
    }

    /**
     * @return array<int, array<string, string|null>>
     *
     * @throws AddressLookupException
     */
    private function fetch(string $postcode): array
    {
        try {
            $response = $this->http
                ->timeout(5)
                ->get(self::ENDPOINT.urlencode($postcode), [
                    'api_key' => config('lunar.checkout.address_lookup.ideal_postcodes.key'),
                ]);
        } catch (ConnectionException $e) {
            throw new AddressLookupException('The address lookup vendor could not be reached.', previous: $e);
        }

        /*
         * A 404 means "no such postcode", which is an answer, not a failure.
         * Everything else non-2xx is the vendor failing us: a bad key, a quota,
         * an outage.
         */
        if ($response->status() === 404) {
            return [];
        }

        if (! $response->successful()) {
            throw new AddressLookupException('The address lookup vendor returned '.$response->status().'.');
        }

        return array_values((array) $response->json('result', []));
    }

    /**
     * Uppercase, whitespace stripped. The vendor accepts either form; the point
     * is that one postcode maps to exactly one cache key.
     */
    private function normalise(string $postcode): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($postcode)) ?? '');
    }

    /**
     * The vendor returns "" for absent fields; CheckoutAddress wants null.
     *
     * @param  array<string, string|null>  $row
     */
    private function value(array $row, string $key): ?string
    {
        $value = trim((string) ($row[$key] ?? ''));

        return $value === '' ? null : $value;
    }
}
