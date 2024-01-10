<?php

namespace Lunar\Shipping\Filament\Tables\Filters;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Shipping\Models\ShippingZone;

class ShippingZoneFilter extends SelectFilter
{
    public function setUp(): void
    {
        parent::setUp();

        $this->label(
            __('shipping::filters.shipping_zone.label')
        );

        $this->options(
            ShippingZone::all()->pluck('name', 'name')
        );
    }

    public function apply(Builder $query, array $data = []): Builder
    {
        $query->when(
            $data['value'] ?? null,
            fn (Builder $query, $value) => $query->where('meta->shipping_zone', $value)
        );

        return $query;
    }
}
