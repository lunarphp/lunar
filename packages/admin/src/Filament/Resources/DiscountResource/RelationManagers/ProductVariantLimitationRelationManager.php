<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
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
                CreateAction::make()->schema([
                    MorphToSelect::make('discountable')
                        ->searchable(true)
                        ->types([
                            Type::make(ProductVariant::modelClass())
                                ->titleAttribute('sku')
                                ->searchColumns(['sku'])
                                ->getSearchResultsUsing(static function (Select $component, string $search): array {
                                    $products = get_search_builder(Product::modelClass(), $search)
                                        ->get();

                                    return ProductVariant::whereIn('product_id', $products->pluck('id'))
                                        ->with(['product'])
                                        ->get()
                                        ->mapWithKeys(fn (ProductVariantContract $record): array => [$record->getKey() => $record->product->attr('name').' - '.$record->sku])
                                        ->all();
                                })
                                ->getOptionLabelUsing(function ($value): string {
                                    $variant = ProductVariant::modelClass()::with('product')->find($value);

                                    return $variant ? $variant->product->attr('name').' - '.$variant->sku : $value;
                                }),
                        ]),
                ])->label(
                    __('lunarpanel::discount.relationmanagers.productvariants.actions.attach.label')
                )->mutateDataUsing(function (array $data) {
                    $data['type'] = 'limitation';

                    return $data;
                }),
            ])->columns([
                TextColumn::make('discountable')
                    ->formatStateUsing(
                        fn (Model $model) => $model->discountable->getDescription()
                    )
                    ->label(
                        __('lunarpanel::discount.relationmanagers.productvariants.table.name.label')
                    ),
                TextColumn::make('discountable.sku')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.productvariants.table.sku.label')
                    ),
                TextColumn::make('discountable.values')
                    ->formatStateUsing(function (Model $record) {
                        return $record->discountable->values->map(
                            fn ($value) => $value->translate('name')
                        )->join(', ');
                    })->label(
                        __('lunarpanel::discount.relationmanagers.productvariants.table.values.label')
                    ),
            ])->recordActions([
                DeleteAction::make(),
            ])->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
