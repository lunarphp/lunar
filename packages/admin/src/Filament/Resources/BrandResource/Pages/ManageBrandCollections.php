<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Core\Models\Collection;
use Lunar\Filament\Forms\Components\CollectionSelect;
use Lunar\Filament\Tables\Columns\TranslatedTextColumn;

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
        return parent::table($table);
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table->columns([
            TranslatedTextColumn::make('attribute_data.name')
                ->description(fn (Collection $record): string => $record->breadcrumb->implode(' > '))
                ->attributeData()
                ->limitedTooltip()
                ->limit(50)
                ->label(__('lunarpanel::product.table.name.label')),
        ])->recordActions([
            DetachAction::make(),
        ])->headerActions([
            AttachAction::make()
                ->recordSelect(fn (Select $select) => CollectionSelect::applyTo($select)
                    ->placeholder(__('lunarpanel::brand.pages.collections.table.header_actions.attach.record_select.placeholder'))),
        ]);
    }
}
