<?php

namespace Lunar\Core\Actions\Taxes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Core\Models\TaxZonePostcode;

class GetTaxZonePostcode
{
    /**
     * Execute the action.
     *
     * A postcode only identifies a place within its country, so when the
     * country is known, only that country's postcode zones, and zones with no
     * country, are considered.
     *
     * @param  string  $postcode
     * @return null|TaxZonePostcode
     */
    public function execute($postcode, ?int $countryId = null)
    {
        $postcodeZone = $this->getZoneMatches($postcode, $countryId);

        if ($postcodeZone instanceof TaxZonePostcode) {
            return $postcodeZone;
        }

        if (! $postcodeZone) {
            return null;
        }

        // The candidates only share the postcode's first character, so a zone
        // whose pattern does not match scores 0 and must not be returned: the
        // caller then falls through to the state, country and default zones.
        $match = $postcodeZone->map(function ($pZone) use ($postcode) {
            return [
                'postcode' => $pZone,
                'matches' => $this->matchWildcard($pZone->postcode, $postcode),
            ];
        })->filter(fn (array $candidate): bool => $candidate['matches'] > 0)
            ->sortByDesc('matches')
            ->first();

        return $match['postcode'] ?? null;
    }

    /**
     * Return the zone or zones which match this postcode.
     *
     * @param  string  $postcode
     * @return TaxZonePostcode|Collection
     */
    protected function getZoneMatches($postcode, ?int $countryId = null)
    {
        $postcode = (string) $postcode;

        $query = TaxZonePostcode::whereHas(
            'taxZone',
            fn (Builder $query) => $query->where('active', true)
        )->when($countryId, fn (Builder $query) => $query->where(
            fn (Builder $query) => $query->where('country_id', $countryId)->orWhereNull('country_id')
        ));

        if ($zone = $query->clone()->wherePostcode($postcode)->first()) {
            return $zone;
        }

        return $query->clone()->where('postcode', 'LIKE', "{$postcode[0]}%")->get();
    }

    /**
     * Match wildcard postcodes and return number of matches.
     *
     * @param  string  $wildcard
     * @param  string  $haystack
     * @return int
     */
    private function matchWildcard($wildcard, $haystack)
    {
        $wildcard = array_map(function ($val) {
            if ($val === '*') {
                return $val;
            }

            return "([{$val}])";
        }, str_split($wildcard));

        $regex = implode('', $wildcard);

        $regex = str_replace(
            ['*', '\*', '\?'], // wildcard chars
            ['.*', '.'],   // regexp chars
            $regex
        );

        preg_match_all('/^'.$regex.'$/is', $haystack, $matches, PREG_SET_ORDER);

        return count(
            ($matches[0] ?? [])
        );
    }
}
