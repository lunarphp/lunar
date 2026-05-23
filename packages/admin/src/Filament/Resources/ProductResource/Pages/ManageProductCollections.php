<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Events\ProductCollectionsUpdated;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;
use Lunar\Filament\Forms\Components\CollectionSelect;
use Lunar\Filament\Tables\Columns\TranslatedTextColumn;

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
        return parent::table($table);
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('position')
            ->columns([
                TranslatedTextColumn::make('attribute_data.name')
                    ->description(fn (CollectionContract $record): string => $record->breadcrumb->implode(' > '))
                    ->attributeData()
                    ->limitedTooltip()
                    ->limit(50)
                    ->label(__('lunarpanel::product.table.name.label')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelect(fn (Select $select) => CollectionSelect::applyTo($select)
                        ->placeholder(__('lunarpanel::product.pages.collections.select_collection')))
                    ->after(
                        fn () => ProductCollectionsUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
            ])
            ->recordActions([
                DetachAction::make()->after(
                    fn () => ProductCollectionsUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->after(
                        fn () => ProductCollectionsUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
                ]),
            ]);
    }
}
