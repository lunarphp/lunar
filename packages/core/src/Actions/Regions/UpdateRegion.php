<?php

namespace Lunar\Core\Actions\Regions;

use Lunar\Core\Contracts\Actions\Regions\UpdatesRegion;
use Lunar\Core\Exceptions\RegionActionException;
use Lunar\Core\Models\Region;

/**
 * Update a region, ensuring at most one region is ever marked default. The
 * default flag moves by promoting another region, never by unsetting. When
 * supplied, the `countries` key replaces the region's country coverage.
 */
class UpdateRegion implements UpdatesRegion
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Region $region, array $attributes): Region
    {
        if ($region->default && array_key_exists('default', $attributes) && ! $attributes['default']) {
            throw new RegionActionException('Cannot unset the default region. Make another region the default instead.');
        }

        $countries = array_key_exists('countries', $attributes) ? $attributes['countries'] : null;
        unset($attributes['countries']);

        if ($attributes['default'] ?? false) {
            Region::query()->where('default', true)->where('id', '!=', $region->id)->update(['default' => false]);
        }

        $region->update($attributes);

        if ($countries !== null) {
            $region->countries()->sync($countries);
        }

        return $region;
    }
}
