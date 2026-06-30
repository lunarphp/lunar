<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Filament\Forms\Components\TranslatedText;

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

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return parent::table($table);
    }

    protected function getDefaultTable(Table $table): Table
    {
        $record = $this->getOwnerRecord();

        return $table->columns([
            TextColumn::make('name')
                ->label(
                    __('lunarpanel::collection.pages.children.table.name.label')
                )
                ->formatStateUsing(fn (Model $record): string => $record->translate('name')),
            TextColumn::make('children_count')->counts('children')
                ->label(
                    __('lunarpanel::collection.pages.children.table.children_count.label')
                ),
        ])->recordActions([
            ViewAction::make()->url(function (Model $record) {
                return CollectionResource::getUrl('edit', ['record' => $record]);
            }),
        ])->headerActions([
            CreateAction::make('createChildCollection')
                ->label(__('lunar-filament::collection.pages.children.actions.create_child.label'))
                ->createAnother(false)
                ->schema([
                    TranslatedText::make('name')->required(),
                ])
                ->action(fn (array $data, Table $table) => app(CreatesChildCollection::class)->execute(
                    parent: $table->getRelationship()->getParent(),
                    name: $data['name'],
                )),
        ]);
    }
}
