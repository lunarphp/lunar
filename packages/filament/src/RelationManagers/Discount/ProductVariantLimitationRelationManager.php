<?php

namespace Lunar\Filament\RelationManagers\Discount;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Forms\Components\DiscountTargetSelect;
use Lunar\Filament\RelationManagers\BaseRelationManager;

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
                __('lunar-filament::discount.relationmanagers.productvariants.title')
            )
            ->description(
                __('lunar-filament::discount.relationmanagers.productvariants.description')
            )
            ->paginated(false)
            ->modifyQueryUsing(
                fn ($query) => $query->whereIn('type', ['limitation', 'exclusion'])
                    ->whereDiscountableType(ProductVariant::morphName())
                    ->whereHas('discountable')
            )
            ->headerActions([
                CreateAction::make()->schema([
                    DiscountTargetSelect::make('discountable')
                        ->targets([ProductVariant::class]),
                ])->label(
                    __('lunar-filament::discount.relationmanagers.productvariants.actions.attach.label')
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
                        __('lunar-filament::discount.relationmanagers.productvariants.table.name.label')
                    ),
                TextColumn::make('discountable.sku')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.productvariants.table.sku.label')
                    ),
                TextColumn::make('discountable.values')
                    ->formatStateUsing(function (Model $record) {
                        return $record->discountable->values->map(
                            fn ($value) => $value->translate('name')
                        )->join(', ');
                    })->label(
                        __('lunar-filament::discount.relationmanagers.productvariants.table.values.label')
                    ),
            ])->recordActions([
                DeleteAction::make(),
            ])->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
