<?php

namespace Lunar\Filament\Tables\TaxZone;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;

class TaxZoneTable
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
                ->label(__('lunar-filament::taxzone.table.name.label')),
            TextColumn::make('default_indicator')
                ->state(fn (Model $record) => $record->default ? __('lunar-filament::taxzone.table.default.label') : null)
                ->badge()
                ->color('gray')
                ->label(''),
            TextColumn::make('zone_type')
                ->label(__('lunar-filament::taxzone.table.zone_type.label'))
                ->formatStateUsing(
                    fn ($state) => __("lunar-filament::taxzone.form.zone_type.options.{$state}")
                ),
            IconColumn::make('active')
                ->boolean()
                ->label(__('lunar-filament::taxzone.table.active.label')),
        ];
    }
}
