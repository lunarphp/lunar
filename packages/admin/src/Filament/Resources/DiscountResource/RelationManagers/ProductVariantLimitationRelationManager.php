<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductVariantLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'discountables';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
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
                    ->whereDiscountableType(ProductVariant::morphName())
                    ->whereHas('discountable')
            )
            ->headerActions([
                Tables\Actions\CreateAction::make()->form([
                    Forms\Components\MorphToSelect::make('discountable')
                        ->searchable(true)
                        ->types([
                            Forms\Components\MorphToSelect\Type::make(ProductVariant::modelClass())
                                ->titleAttribute('sku')
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                    $products = get_search_builder(Product::modelClass(), $search)
                                        ->get();

                                    return ProductVariant::whereIn('product_id', $products->pluck('id'))
                                        ->with(['product'])
                                        ->get()
                                        ->mapWithKeys(fn (ProductVariantContract $record): array => [$record->getKey() => $record->product->attr('name').' - '.$record->sku])
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
                Tables\Columns\TextColumn::make('discountable')
                    ->formatStateUsing(
                        fn (Model $model) => $model->discountable->getDescription()
                    )
                    ->label(
                        __('lunarpanel::discount.relationmanagers.productvariants.table.name.label')
                    ),
                Tables\Columns\TextColumn::make('discountable.sku')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.productvariants.table.sku.label')
                    ),
                Tables\Columns\TextColumn::make('discountable.values')
                    ->formatStateUsing(function (Model $record) {
                        return $record->discountable->values->map(
                            fn ($value) => $value->translate('name')
                        )->join(', ');
                    })->label(
                        __('lunarpanel::discount.relationmanagers.productvariants.table.values.label')
                    ),
            ])->actions([
                Tables\Actions\DeleteAction::make(),
            ])->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
