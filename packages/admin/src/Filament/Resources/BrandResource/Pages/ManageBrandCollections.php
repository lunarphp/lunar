<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Filament\Forms;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Collection;
use Lunar\Models\Contracts\Collection as CollectionContract;

class ManageBrandCollections extends BaseManageRelatedRecords
{
    protected static string $resource = BrandResource::class;

    protected static string $relationship = 'collections';

    public function getTitle(): string
    {

        return __('lunarpanel::brand.pages.collections.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::collections');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::brand.pages.collections.label');
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TranslatedTextColumn::make('attribute_data.name')
                ->attributeData()
                ->limitedTooltip()
                ->limit(50)
                ->label(__('lunarpanel::product.table.name.label')),
        ])->actions([
            DetachAction::make(),
        ])->headerActions([
            Tables\Actions\AttachAction::make()
                ->recordSelect(
                    function (Forms\Components\Select $select) {
                        return $select->placeholder(
                            __('lunarpanel::brand.pages.collections.table.header_actions.attach.record_select.placeholder')
                        )
                            ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                return Collection::modelClass()::search($search)
                                    ->get()
                                    ->mapWithKeys(fn (CollectionContract $record): array => [$record->getKey() => $record->breadcrumb->push($record->translateAttribute('name'))->join(' > ')])
                                    ->all();
                            });
                    }
                ),
        ]);
    }
}
