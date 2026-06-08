<?php

namespace Lunar\Filament\Tables\Location;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;

class LocationTable
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

    /**
     * @return array<int, TextColumn>
     */
    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunar-filament::location.table.name.label'))
                ->searchable(),
            TextColumn::make('default_indicator')
                ->state(fn (Model $record) => $record->default ? __('lunar-filament::location.table.default.label') : null)
                ->badge()
                ->color('gray')
                ->label(''),
            TextColumn::make('handle')
                ->label(__('lunar-filament::location.table.handle.label')),
            TextColumn::make('fulfilments_count')
                ->counts('fulfilments')
                ->label(__('lunar-filament::location.table.fulfilments.label')),
        ];
    }
}
