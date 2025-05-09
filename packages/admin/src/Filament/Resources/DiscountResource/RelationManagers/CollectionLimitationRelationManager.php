<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductCollections;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Models\Collection;
use Lunar\Models\Contracts\Collection as CollectionContract;

class CollectionLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'collections';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::collection.plural_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {

        return $table
            ->description(
                __('lunarpanel::discount.relationmanagers.collections.description')
            )
            ->paginated(false)
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('type')
                            ->options(
                                fn () => [
                                    'limitation' => __('lunarpanel::discount.relationmanagers.collections.form.type.options.limitation.label'),
                                    'exclusion' => __('lunarpanel::discount.relationmanagers.collections.form.type.options.exclusion.label'),
                                ]
                            )->default('limitation'),
                    ])
                    ->recordSelect(
                        function (Forms\Components\Select $select) {
                            return $select->relationship(name: 'collections')
                                ->options(function () {
                                    return Collection::limit(50)->get()
                                        ->mapWithKeys(fn (Collection $record): array => [$record->getKey() => $record->breadcrumb->push($record->translateAttribute('name'))->join(' > ')])
                                        ->all();
                                })
                                ->required()
                                ->searchable(true)
                                ->preload()
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search, ManageProductCollections $livewire): array {
                                    $relationModel = $livewire->getRelationship()->getRelated()::class;

                                    return get_search_builder($relationModel, $search)
                                        ->get()
                                        ->mapWithKeys(fn (CollectionContract $record): array => [$record->getKey() => $record->breadcrumb->push($record->translateAttribute('name'))->join(' > ')])
                                        ->all();
                                });
                        }
                    )
                    ->recordTitle(function ($record) {
                        return $record->attr('name');
                    })
                    ->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collections.actions.attach.label')
                    ),
            ])->columns([
                Tables\Columns\TextColumn::make('attribute_data.name')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collections.table.name.label')
                    )
                    ->description(fn (Collection $record): string => $record->breadcrumb->implode(' > '))
                    ->formatStateUsing(
                        fn (Model $record) => $record->attr('name')
                    ),
                Tables\Columns\TextColumn::make('pivot.type')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collections.table.type.label')
                    )->formatStateUsing(
                        fn (string $state) => __("lunarpanel::discount.relationmanagers.collections.table.type.{$state}.label")
                    ),
            ])->actions([
                Tables\Actions\DetachAction::make(),
            ])->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
