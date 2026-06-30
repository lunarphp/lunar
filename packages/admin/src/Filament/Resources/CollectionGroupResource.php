<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages\EditCollectionGroup;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages\ListCollectionGroups;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Filament\Schemas\CollectionGroup\CollectionGroupForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\CollectionGroup\CollectionGroupTable;

class CollectionGroupResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-collections';

    protected static ?string $model = CollectionGroup::class;

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
        return Resolver::form(CollectionGroupForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(CollectionGroupTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListCollectionGroups::route('/'),
            'edit' => EditCollectionGroup::route('/{record}/edit'),
        ];
    }
}
