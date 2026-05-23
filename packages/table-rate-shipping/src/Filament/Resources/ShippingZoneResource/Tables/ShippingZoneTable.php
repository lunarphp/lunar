<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ShippingZoneTable
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
                ->label(__('lunarpanel.shipping::shippingzone.table.name.label')),
            TextColumn::make('type')
                ->label(__('lunarpanel.shipping::shippingzone.table.type.label'))
                ->formatStateUsing(
                    fn ($state) => __("lunarpanel.shipping::shippingzone.table.type.options.{$state}")
                ),
        ];
    }
}
