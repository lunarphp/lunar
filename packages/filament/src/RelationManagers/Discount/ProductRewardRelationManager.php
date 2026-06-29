<?php

namespace Lunar\Filament\RelationManagers\Discount;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Forms\Components\DiscountTargetSelect;
use Lunar\Filament\RelationManagers\BaseRelationManager;
use Lunar\Filament\Tables\Columns\ThumbnailImageColumn;

class ProductRewardRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'discountables';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::discount.relationmanagers.rewards.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {

        return $table
            ->heading(
                __('lunar-filament::discount.relationmanagers.rewards.title')
            )
            ->description(
                __('lunar-filament::discount.relationmanagers.rewards.description')
            )
            ->paginated(false)
            ->modifyQueryUsing(
                fn ($query) => $query->whereIn('type', ['reward'])
                    ->whereIn('discountable_type', [Product::morphName(), ProductVariant::morphName(), Collection::morphName()])
                    ->whereHas('discountable')
            )
            ->headerActions([
                CreateAction::make()->schema([
                    DiscountTargetSelect::make('discountable')
                        ->targets([Product::class, ProductVariant::class, Collection::class]),
                ])->label(
                    __('lunar-filament::discount.relationmanagers.rewards.actions.attach.label')
                )->mutateDataUsing(function (array $data) {
                    $data['type'] = 'reward';

                    return $data;
                }),
            ])->columns([
                ThumbnailImageColumn::make('discountable_id')
                    ->resolveThumbnailUrlUsing(fn (?Model $record) => $record?->discountable?->getThumbnailImage())
                    ->label(''),

                TextColumn::make('discountable.id')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.conditions.table.name.label')
                    )
                    ->formatStateUsing(function (Model $record) {
                        if ($record->discountable instanceof ProductVariant) {
                            return $record->discountable->product->translate('name').' - '.$record->discountable->sku;
                        }

                        return $record->discountable?->translate('name');
                    }),

                TextColumn::make('discountable_type')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.conditions.table.type.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->discountable ? str($record->discountable->morphName())->replace('_', ' ')->title() : null,
                    ),

            ])->recordActions([
                DeleteAction::make(),
            ])->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
