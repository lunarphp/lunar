<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages\EditCollectionGroup;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages\ListCollectionGroups;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Schemas\CollectionGroupForm;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Tables\CollectionGroupTable;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\CollectionGroup as CollectionGroupContract;

class CollectionGroupResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-collections';

    protected static ?string $model = CollectionGroupContract::class;

    protected static ?int $navigationSort = 3;

    public static function getLabel(): string
    {
        return __('lunarpanel::collectiongroup.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::collectiongroup.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::collections');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return CollectionGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollectionGroupTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollectionGroups::route('/'),
            'edit' => EditCollectionGroup::route('/{record}/edit'),
        ];
    }
}
