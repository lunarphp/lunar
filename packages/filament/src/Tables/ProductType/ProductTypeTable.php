<?php

namespace Lunar\Filament\Tables\ProductType;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Lunar\Core\Actions\ProductTypes\DeleteProductType;
use Lunar\Core\Models\ProductType;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ProductTypeTable
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
                        DeleteBulkAction::make()
                            ->before(function (DeleteBulkAction $action, Collection $records) {
                                if ($records->contains(fn (ProductType $productType) => DeleteProductType::isProtected($productType))) {
                                    Notification::make()
                                        ->warning()
                                        ->body(__('lunarpanel::producttype.action.delete.notification.error_protected'))
                                        ->send();
                                    $action->cancel();
                                }
                            }),
                    ]),
                ]),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunar-filament::producttype.table.name.label')),
            TextColumn::make('products_count')
                ->counts('products')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunar-filament::producttype.table.products_count.label')),
            TextColumn::make('product_attributes_count')
                ->counts('productAttributes')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunar-filament::producttype.table.product_attributes_count.label')),
            TextColumn::make('variant_attributes_count')
                ->counts('variantAttributes')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunar-filament::producttype.table.variant_attributes_count.label'))
                ->visible(
                    config('lunar.filament.enable_variants', true)
                ),
        ];
    }
}
