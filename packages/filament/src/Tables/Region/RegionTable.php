<?php

namespace Lunar\Filament\Tables\Region;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;

class RegionTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([])
                ->recordActions([
                    EditAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunar-filament::region.table.name.label')),
            TextColumn::make('default_indicator')
                ->state(fn (Model $record) => $record->default ? __('lunar-filament::region.table.default.label') : null)
                ->badge()
                ->color('gray')
                ->label(''),
            TextColumn::make('channel.name')
                ->label(__('lunar-filament::region.table.channel.label')),
            TextColumn::make('currency.code')
                ->label(__('lunar-filament::region.table.currency.label')),
            TextColumn::make('language.code')
                ->label(__('lunar-filament::region.table.language.label')),
        ];
    }
}
