<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\Tables\Actions\Collections\CreateChildCollection;

class ManageCollectionChildren extends BaseManageRelatedRecords
{
    protected static string $resource = CollectionResource::class;

    protected static string $relationship = 'children';

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::collection.pages.children.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::collections');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::collection.pages.children.label');
    }

    public function getBreadcrumbs(): array
    {
        $record = $this->getRecord();

        $crumbs = static::getResource()::getCollectionBreadcrumbs($record);

        $crumbs[] = $this->getBreadcrumb();

        return $crumbs;
    }

    public function getBreadcrumb(): string
    {
        return __('lunarpanel::collection.pages.children.label');
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    public function table(Table $table): Table
    {
        $record = $this->getOwnerRecord();

        return $table->columns([
            Tables\Columns\TextColumn::make('attribute_data.name')
                ->label(
                    __('lunarpanel::collection.pages.children.table.name.label')
                )
                ->formatStateUsing(fn (Model $record): string => $record->attr('name')),
            Tables\Columns\TextColumn::make('children_count')->counts('children')
                ->label(
                    __('lunarpanel::collection.pages.children.table.children_count.label')
                ),
        ])->actions([
            Tables\Actions\ViewAction::make()->url(function (Model $record) {
                return CollectionResource::getUrl('edit', ['record' => $record]);
            }),
        ])->headerActions([
            CreateChildCollection::make('createChildCollection'),
        ]);
    }
}
