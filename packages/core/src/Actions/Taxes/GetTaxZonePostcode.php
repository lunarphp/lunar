<?php

namespace GetCandy\Actions\Taxes;

use GetCandy\Models\TaxZonePostcode;

class GetTaxZonePostcode
{
    /**
     * Execute the action.
     *
     * @param  string  $postcode
     * @return null|\GetCandy\Models\TaxZonePostcode
     */
    public function execute($postcode)
    {
        $postcodeZone = $this->getZoneMatches($postcode);

        if ($postcodeZone instanceof TaxZonePostcode) {
            return $postcodeZone;
        }

        if (! $postcodeZone) {
            return null;
        }

        $match = $postcodeZone->map(function ($pZone) use ($postcode) {
            return [
                'postcode' => $pZone,
                'matches'  => $this->matchWildcard($pZone->postcode, $postcode),
            ];
        })->sort(fn ($current, $next) => $current['matches'] < $next['matches'])->first();

        // Give up, use default...
        if (! $match) {
            return null;
        }

        return $match ? $match['postcode'] : null;
    }

    /**
     * Return the zone or zones which match this postcode.
     *
     * @param  string  $postcode
     * @return \GetCandy\Models\TaxZonePostcode|\Illuminate\Support\Collection
     */
    protected function getZoneMatches($postcode)
    {
        $postcode = (string) $postcode;

        if ($zone = TaxZonePostcode::wherePostcode($postcode)->first()) {
            return $zone;
        }

        return TaxZonePostcode::where('postcode', 'LIKE', "{$postcode[0]}%")->get();
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
