<?php

namespace Lunar\Filament\Tables\Currency;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;

class CurrencyTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table->columns(static::getColumns()),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunar-filament::currency.table.name.label')),
            TextColumn::make('default_indicator')
                ->state(fn (Model $record) => $record->default ? __('lunar-filament::currency.table.default.label') : null)
                ->badge()
                ->color('gray')
                ->label(''),
            TextColumn::make('code')
                ->label(__('lunar-filament::currency.table.code.label')),
            TextColumn::make('exchange_rate')
                ->label(__('lunar-filament::currency.table.exchange_rate.label')),
            TextColumn::make('decimal_places')
                ->label(__('lunar-filament::currency.table.decimal_places.label')),
            IconColumn::make('enabled')
                ->boolean()
                ->label(__('lunar-filament::currency.table.enabled.label')),
            IconColumn::make('sync_prices')
                ->boolean()
                ->label(__('lunar-filament::currency.table.sync_prices.label')),
        ];
    }
}
