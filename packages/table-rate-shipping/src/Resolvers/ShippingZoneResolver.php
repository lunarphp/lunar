<?php

namespace Lunar\Shipping\Resolvers;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Shipping\DataTransferObjects\PostcodeLookup;
use Lunar\Shipping\Models\ShippingZone;

class ShippingZoneResolver
{
    /**
     * The country to use when resolving zones.
     */
    protected ?Country $country = null;

    /**
     * The state to use when resolving zones.
     */
    protected ?State $state = null;

    /**
     * The postcode lookup to use when resolving zones.
     */
    protected ?PostcodeLookup $postcodeLookup = null;

    /**
     * The type of zones we want to query.
     */
    protected Collection $types;

    /**
     * Initialise the resolver.
     */
    public function __construct()
    {
        $this->types = collect();
    }

    /**
     * Set the country.
     */
    public function country(?Country $country = null): self
    {
        $this->country = $country;
        $this->types->push('countries');

        return $this;
    }

    /**
     * Set the state.
     */
    public function state(?State $state = null): self
    {
        $this->state = $state;
        $this->types->push('states');

        return $this;
    }

    /**
     * Set the postcode to use when resolving.
     */
    public function postcode(PostcodeLookup $postcodeLookup): self
    {
        $this->postcodeLookup = $postcodeLookup;
        $this->types->push('postcodes');

        return $this;
    }

    /**
     * Return the shipping zones based on the criteria.
     */
    public function get(): Collection
    {
        $query = ShippingZone::query()->whereType('unrestricted');

        $query->orWhere(function ($builder) {
            if ($this->country) {
                $builder->orWhere(function ($qb) {
                    $qb->whereHas('countries', function ($query) {
                        $query->where('country_id', $this->country->id);
                    })->whereType('countries');
                });
            }

            if ($this->state) {
                $builder->orWhere(function ($qb) {
                    $qb->whereHas('states', function ($query) {
                        $query->where('state_id', $this->state->id);
                    })->whereType('states');
                });
            }

            if ($this->postcodeLookup) {
                $builder->orWhere(function ($qb) {
                    $qb->whereHas('postcodes', function ($query) {
                        $query->whereIn('postcode', $this->postcodeLookup->getParts());
                    })->where(function ($qb) {
                        $qb->whereHas('countries', function ($query) {
                            $query->where('country_id', $this->postcodeLookup->country->id);
                        });
                    })->whereType('postcodes');
                })->orWhere(function ($qb) {
                    $qb->whereHas('countries', function ($query) {
                        $query->where('country_id', $this->postcodeLookup->country->id);
                    })->whereType('countries');
                });
            }
        });

        $zones = $query->get();

        return $this->postcodeLookup ? $this->preferMostSpecificPostcodeZones($zones) : $zones;
    }

    /**
     * Postcode zones overlap by design: an area zone ("AB") and a district
     * zone ("AB36") both match an AB36 postcode. The zone matched by the most
     * specific part of the postcode wins and the rest are dropped, so a
     * district can be priced, or excluded, differently from its area.
     * Country and state zones are untouched.
     *
     * @param  Collection<int, ShippingZone>  $zones
     * @return Collection<int, ShippingZone>
     */
    protected function preferMostSpecificPostcodeZones(Collection $zones): Collection
    {
        $parts = $this->postcodeLookup->getParts()->values();

        $ranks = $zones
            ->where('type', 'postcodes')
            ->mapWithKeys(function (ShippingZone $zone) use ($parts) {
                $rank = $zone->postcodes()
                    ->whereIn('postcode', $parts)
                    ->pluck('postcode')
                    ->map(fn (string $postcode): int|false => $parts->search($postcode))
                    ->filter(fn (int|false $index): bool => $index !== false)
                    ->min();

                return [$zone->id => $rank ?? PHP_INT_MAX];
            });

        if ($ranks->isEmpty()) {
            return $zones;
        }

        $best = $ranks->min();

        return $zones
            ->reject(fn (ShippingZone $zone): bool => $zone->type === 'postcodes' && $ranks[$zone->id] !== $best)
            ->values();
    }
}
