<?php

namespace Lunar\Shipping\Filament\Resources\ShippingMethodResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Support\Concerns\CallsHooks;

class ShippingMethodTable
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
                ->label(__('lunarpanel.shipping::shippingmethod.table.name.label')),
            TextColumn::make('code')
                ->label(__('lunarpanel.shipping::shippingmethod.table.code.label')),
            TextColumn::make('driver')
                ->label(__('lunarpanel.shipping::shippingmethod.table.driver.label'))
                ->formatStateUsing(
                    fn ($state) => __("lunarpanel.shipping::shippingmethod.table.driver.options.{$state}")
                ),
        ];
    }
}
