<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Models\Collection;
use Lunar\Models\Contracts\Collection as CollectionContract;
use Lunar\Models\Contracts\Product as ProductContract;
use Lunar\Models\Product;

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
                    ->whereIn('discountable_type', [Collection::morphName(), Product::morphName()])
                    ->whereHas('discountable')
            )
            ->headerActions([
                Tables\Actions\CreateAction::make()->form([
                    Forms\Components\MorphToSelect::make('discountable')
                        ->searchable(true)
                        ->types([
                            Forms\Components\MorphToSelect\Type::make(Product::modelClass())
                                ->titleAttribute('name.en')
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                    return get_search_builder(Product::modelClass(), $search)
                                        ->get()
                                        ->mapWithKeys(fn (ProductContract $record): array => [$record->getKey() => $record->attr('name')])
                                        ->all();
                                }),

                            Forms\Components\MorphToSelect\Type::make(Collection::modelClass())
                                ->titleAttribute('name.en')
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                    return get_search_builder(Collection::modelClass(), $search)
                                        ->get()
                                        ->mapWithKeys(fn (CollectionContract $record): array => [$record->getKey() => $record->attr('name')])
                                        ->all();
                                }),
                        ]),
                ])->label(
                    __('lunarpanel::discount.relationmanagers.conditions.actions.attach.label')
                )->mutateFormDataUsing(function (array $data) {
                    $data['type'] = 'condition';

                    return $data;
                }),
            ])->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('discountable.thumbnail')
                    ->collection(config('lunar.media.collection'))
                    ->conversion('small')
                    ->limit(1)
                    ->square()
                    ->label(''),

                Tables\Columns\TextColumn::make('discountable.attribute_data.name')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.conditions.table.name.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->discountable->attr('name')
                    ),

                Tables\Columns\TextColumn::make('discountable_type')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.conditions.table.type.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => ucfirst($record->discountable->morphName()),
                    ),
            ])->actions([
                Tables\Actions\DeleteAction::make(),
            ])->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
