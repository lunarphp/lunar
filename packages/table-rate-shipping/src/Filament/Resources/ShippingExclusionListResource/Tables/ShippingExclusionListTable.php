<?php

namespace Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Support\Concerns\CallsHooks;

class ShippingExclusionListTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters([])
                ->actions([
                    EditAction::make(),
                ])
                ->bulkActions([
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
                ->label(__('lunarpanel.shipping::shippingexclusionlist.table.name.label')),
            TextColumn::make('exclusions_count')
                ->label(__('lunarpanel.shipping::shippingexclusionlist.table.exclusions_count.label'))
                ->counts('exclusions'),
        ];
    }
}
