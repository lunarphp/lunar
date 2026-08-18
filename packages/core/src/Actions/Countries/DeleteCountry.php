<?php

namespace Lunar\Core\Actions\Countries;

use Lunar\Core\Contracts\Actions\Countries\DeletesCountry;
use Lunar\Core\Exceptions\CountryActionException;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\TaxZoneCountry;

/**
 * Delete a country. Countries with states, address references, or tax zone
 * coverage are kept — they are live reference data other records point at.
 */
class DeleteCountry implements DeletesCountry
{
    public function execute(Country $country): void
    {
        if ($country->states()->exists()) {
            throw new CountryActionException('Cannot delete a country with states. Remove its states first.');
        }

        if (Address::query()->where('country_id', $country->id)->exists()) {
            throw new CountryActionException('Cannot delete a country referenced by addresses.');
        }

        if (TaxZoneCountry::query()->where('country_id', $country->id)->exists()) {
            throw new CountryActionException('Cannot delete a country referenced by a tax zone.');
        }

        $country->delete();
    }
}
