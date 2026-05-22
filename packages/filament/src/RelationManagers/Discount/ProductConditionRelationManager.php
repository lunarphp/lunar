<?php

namespace Lunar\Filament\RelationManagers\Discount;

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
use Lunar\Filament\Tables\Columns\ThumbnailImageColumn;
use Lunar\Core\Models\Contracts\Product as ProductContract;
use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

class ProductConditionRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'discountables';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::discount.relationmanagers.conditions.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {
        $prefix = config('lunar.database.table_prefix');

        return $table
            ->heading(
                __('lunarpanel::discount.relationmanagers.conditions.title')
            )
            ->description(
                __('lunarpanel::discount.relationmanagers.conditions.description')
            )
            ->paginated(false)
            ->modifyQueryUsing(
                fn ($query) => $query->whereIn('type', ['condition'])
                    ->whereIn('discountable_type', [Product::morphName(), ProductVariant::morphName()])
                    ->whereHas('discountable')
            )
            ->headerActions([
                CreateAction::make()->schema([
                    MorphToSelect::make('discountable')
                        ->searchable(true)
                        ->types([
                            Type::make(Product::modelClass())
                                ->titleAttribute('name.en')
                                ->getSearchResultsUsing(static function (Select $component, string $search): array {
                                    return get_search_builder(Product::modelClass(), $search)
                                        ->get()
                                        ->mapWithKeys(fn (ProductContract $record): array => [$record->getKey() => $record->attr('name')])
                                        ->all();
                                })
                                ->getOptionLabelUsing(function ($value): string {
                                    return Product::modelClass()::find($value)?->attr('name') ?? $value;
                                }),

                            Type::make(ProductVariant::modelClass())
                                ->titleAttribute('sku')
                                ->getSearchResultsUsing(static function (Select $component, string $search): array {
                                    return get_search_builder(ProductVariant::modelClass(), $search)
                                        ->orWhere('sku', 'like', $search.'%')
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
                    __('lunarpanel::discount.relationmanagers.conditions.actions.attach.label')
                )->mutateDataUsing(function (array $data) {
                    $data['type'] = 'condition';

                    return $data;
                }),
            ])->columns([
                ThumbnailImageColumn::make('discountable_id')
                    ->resolveThumbnailUrlUsing(fn (?Model $record) => $record?->discountable?->getThumbnailImage())
                    ->label(''),

                TextColumn::make('discountable.id')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.conditions.table.name.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->discountable instanceof ProductVariantContract ? $record->discountable->product->attr('name').' - '.$record->discountable->sku : $record->discountable->attr('name')
                    ),

                TextColumn::make('discountable_type')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.conditions.table.type.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => str($record->discountable->morphName())->replace('_', ' ')->title(),
                    ),
            ])->recordActions([
                DeleteAction::make(),
            ])->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
