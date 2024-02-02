<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductVariantLimitationRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'purchasables';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {

        return $table
            ->heading(
                __('lunarpanel::discount.relationmanagers.productvariants.title')
            )
            ->description(
                __('lunarpanel::discount.relationmanagers.productvariants.description')
            )
            ->paginated(false)
            ->modifyQueryUsing(
                fn ($query) => $query->whereIn('type', ['limitation', 'exclusion'])
                    ->wherePurchasableType(ProductVariant::class)
                    ->whereHas('purchasable')
            )
            ->headerActions([
                Tables\Actions\CreateAction::make()->form([
                    Forms\Components\MorphToSelect::make('purchasable')
                        ->searchable(true)
                        ->types([
                            Forms\Components\MorphToSelect\Type::make(ProductVariant::class)
                                ->titleAttribute('sku')
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                    $products = Product::search($search)
                                        ->get();

                                    return ProductVariant::whereIn('product_id', $products->pluck('id'))
                                        ->get()
                                        ->mapWithKeys(fn (ProductVariant $record): array => [$record->getKey() => $record->product->attr('name').' - '.$record->sku])
                                        ->all();
                                }),
                        ]),
                ])->label(
                    __('lunarpanel::discount.relationmanagers.productvariants.actions.attach.label')
                )->mutateFormDataUsing(function (array $data) {
                    $data['type'] = 'limitation';

                    return $data;
                }),
            ])->columns([
            ])->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
