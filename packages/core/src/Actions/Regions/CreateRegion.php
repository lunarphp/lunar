<?php

namespace Lunar\Core\Actions\Regions;

use Lunar\Core\Contracts\Actions\Regions\CreatesRegion;
use Lunar\Core\Models\Region;

/**
 * Create a region, ensuring at most one region is ever marked default. The
 * `countries` key attaches the countries the region covers.
 */
class CreateRegion implements CreatesRegion
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Region
    {
        $countries = $attributes['countries'] ?? null;
        unset($attributes['countries']);

        if ($attributes['default'] ?? false) {
            Region::query()->where('default', true)->update(['default' => false]);
        }

        /** @var Region $region */
        $region = Region::create($attributes);

        if ($countries !== null) {
            $region->countries()->sync($countries);
        }

        return $region;
    }
}
