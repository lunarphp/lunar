<?php

namespace Lunar\Shipping;

use Filament\Contracts\Plugin;
use Filament\Panel;
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
            ShippingZoneResource::class,
        ]);
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugin(BlogPlugin::make());
    }

    // ...
}
