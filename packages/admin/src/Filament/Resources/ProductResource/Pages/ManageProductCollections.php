<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Core\Models\Collection;
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
            ->recordTitle(fn (Collection $record): ?string => $record->translate('name'))
            ->reorderable('position')
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with('ancestors')
            )
            ->columns([
                TranslatedTextColumn::make('name')
                    ->description(fn (Collection $record): string => $record->breadcrumb->implode(' > '))
                    ->fieldHydrated('name')
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
                        ->placeholder(__('lunarpanel::product.pages.collections.select_collection'))),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
