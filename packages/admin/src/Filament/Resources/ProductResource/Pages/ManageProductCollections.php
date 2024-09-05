<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Events\ProductCollectionsUpdated;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Contracts\Collection as CollectionContract;

class ManageProductCollections extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'collections';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::collections');
    }

    public function getTitle(): string
    {
        return __('lunarpanel::product.pages.collections.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.collections.label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TranslatedTextColumn::make('attribute_data.name')
                    ->attributeData()
                    ->limitedTooltip()
                    ->limit(50)
                    ->label(__('lunarpanel::product.table.name.label')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelect(
                        function (Forms\Components\Select $select) {
                            return $select->placeholder('Select a collection') // TODO: needs translation
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search, ManageProductCollections $livewire): array {
                                    $relationModel = $livewire->getRelationship()->getRelated()::class;

                                    return get_search_builder($relationModel, $search)
                                        ->get()
                                        ->mapWithKeys(fn (CollectionContract $record): array => [$record->getKey() => $record->breadcrumb->push($record->translateAttribute('name'))->join(' > ')])
                                        ->all();
                                });
                        }
                    )->after(
                        fn () => ProductCollectionsUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->after(
                    fn () => ProductCollectionsUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->after(
                        fn () => ProductCollectionsUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
                ]),
            ]);
    }
}
