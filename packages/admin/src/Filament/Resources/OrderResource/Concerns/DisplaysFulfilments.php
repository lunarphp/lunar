<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Livewire;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderFulfilments;

trait DisplaysFulfilments
{
    public static function getFulfilmentsTable(): Livewire
    {
        return Livewire::make(
            OrderFulfilments::class,
            fn ($record) => ['record' => $record],
        )->key('lunar_livewire_fulfilments');
    }

    public static function getDefaultFulfilmentsInfolist(): Component
    {
        // Rendered without a wrapping Section — each fulfilment is already its
        // own card, so the outer container is redundant.
        return static::getFulfilmentsTable();
    }

    public static function getFulfilmentsInfolist(): Component
    {
        return self::callStaticLunarHook('extendFulfilmentsInfolist', static::getDefaultFulfilmentsInfolist());
    }
}
