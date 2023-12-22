<?php

namespace Lunar\Shipping;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationItem;
use Filament\Panel;

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
        ])->navigationItems([
            NavigationItem::make('Shipping Zones')
                ->url('#')
                ->icon('lucide-truck')
                ->group('Shipping')
                ->sort(1),
            NavigationItem::make('Shipping Exclusions')
                ->url('#')
                ->icon('lucide-archive-x')
                ->group('Shipping')
                ->sort(1),
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
