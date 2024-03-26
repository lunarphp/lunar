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
                        return $select->placeholder('Select a collection') // TODO: needs translation
                            ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                return Collection::search($search)
                                    ->get()
                                    ->mapWithKeys(fn (Collection $record): array => [$record->getKey() => $record->breadcrumb->push($record->translateAttribute('name'))->join(' > ')])
                                    ->all();
                            });
                    }
                ),
        ]);
    }
}
