<?php

namespace Lunar\Admin\Filament\Resources\CurrencyResource\Tables;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Concerns\CallsHooks;

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
            BadgeableColumn::make('name')
                ->separator('')
                ->suffixBadges([
                    Badge::make('default')
                        ->label(__('lunarpanel::currency.table.default.label'))
                        ->color('gray')
                        ->visible(fn (Model $record) => $record->default),
                ])
                ->label(__('lunarpanel::currency.table.name.label')),
            TextColumn::make('code')
                ->label(__('lunarpanel::currency.table.code.label')),
            TextColumn::make('exchange_rate')
                ->label(__('lunarpanel::currency.table.exchange_rate.label')),
            TextColumn::make('decimal_places')
                ->label(__('lunarpanel::currency.table.decimal_places.label')),
            IconColumn::make('enabled')
                ->boolean()
                ->label(__('lunarpanel::currency.table.enabled.label')),
            IconColumn::make('sync_prices')
                ->boolean()
                ->label(__('lunarpanel::currency.table.sync_prices.label')),
        ];
    }
}
