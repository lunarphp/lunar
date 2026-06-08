<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\FulfilmentsTable;

trait DisplaysFulfilments
{
    public static function getFulfilmentsTable(): Livewire
    {
        return Livewire::make(
            FulfilmentsTable::class,
            fn ($record) => ['record' => $record],
        )->key('lunar_livewire_fulfilments');
    }

    public static function getDefaultFulfilmentsInfolist(): Component
    {
        return Section::make('fulfilments')
            ->heading(__('lunarpanel::order.fulfilments.heading'))
            ->compact()
            ->schema([
                static::getFulfilmentsTable(),
            ]);
    }

    public static function getFulfilmentsInfolist(): Component
    {
        return self::callStaticLunarHook('extendFulfilmentsInfolist', static::getDefaultFulfilmentsInfolist());
    }
}
