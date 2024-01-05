<?php

namespace Lunar\Shipping;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource;

class ShippingPlugin implements Plugin
{
    public function getId(): string
    {
        return 'shipping';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public function register(Panel $panel): void
    {
        $panel->navigationGroups([
            'Shipping',
        ])->resources([
            ShippingMethodResource::class,
            ShippingZoneResource::class,
        ]);

        FilamentIcon::register([
            'lunar::shipping-zones' => 'lucide-globe-2',
            'lunar::shipping-methods' => 'lucide-truck',
        ]);
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel;
    }

    // ...
}
