<?php

namespace Lunar\Shipping\Filament\Extensions;

use Lunar\Admin\Support\Extending\ResourceExtension;
use Lunar\Shipping\Filament\Tables\Filters\ShippingZoneFilter;

class OrderResourceExtension extends ResourceExtension
{
    public function extendTableFilters(array $filters): array
    {
        return [
            ...$filters,
            ShippingZoneFilter::make('shipping_Zone'),
        ];
    }
}
