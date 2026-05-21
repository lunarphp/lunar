<?php

namespace Lunar\Admin\Filament\Resources\TaxRateResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Support\Concerns\CallsHooks;

class TaxRateTable
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
            TextColumn::make('name'),
            TextColumn::make('taxZone.name')
                ->label(__('lunarpanel::taxrate.table.tax_zone.label')),
            TextColumn::make('priority')
                ->label(__('lunarpanel::taxrate.table.priority.label')),
        ];
    }
}
