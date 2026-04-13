<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\EditCollection;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ListCollections;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ManageCollectionAvailability;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ManageCollectionChildren;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ManageCollectionMedia;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ManageCollectionProducts;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ManageCollectionUrls;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Collection as CollectionContract;

class CollectionResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-collections';

    protected static ?string $model = CollectionContract::class;

    protected static int $globalSearchResultsLimit = 5;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel::collection.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::collection.plural_label');
    }

    public static function getNavigationItems(): array
    {
        return [];
    }

    public static function getCollectionBreadcrumbs(CollectionContract $collection): array
    {
        $crumbs = [
            CollectionGroupResource::getUrl('index') => CollectionGroupResource::getPluralLabel(),
            CollectionGroupResource::getUrl('edit', [
                'record' => $collection->group,
            ]) => $collection->group->name,
        ];

        foreach ($collection->ancestors()->defaultOrder()->get() as $childCollection) {
            $crumbs[
            CollectionResource::getUrl('edit', [
                'record' => $childCollection,
            ])
            ] = $childCollection->attr('name');
        }

        $crumbs[
        static::getUrl('edit', [
            'record' => $collection,
        ])] = $collection->attr('name');

        return $crumbs;
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                static::getAttributeDataFormComponent(),
            ])
            ->columns(1);
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make();
    }

    protected static function getMainFormComponents(): array
    {
        return [
        ];
    }

    protected static function getDefaultRelations(): array
    {
        return [];
    }

    public static function getDefaultSubNavigation(): array
    {
        return [
            EditCollection::class,
            ManageCollectionChildren::class,
            ManageCollectionProducts::class,
            ManageCollectionAvailability::class,
            ManageCollectionMedia::class,
            ManageCollectionUrls::class,
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListCollections::route('/'),
            'availability' => ManageCollectionAvailability::route('/{record}/availability'),
            'children' => ManageCollectionChildren::route('/{record}/children'),
            'products' => ManageCollectionProducts::route('/{record}/products'),
            'edit' => EditCollection::route('/{record}/edit'),
            'media' => ManageCollectionMedia::route('/{record}/media'),
            'urls' => ManageCollectionUrls::route('/{record}/urls'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->translateAttribute('name');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'group.name', // Needed to trig canGloballySearch()
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with([
            'group',
        ]);
    }
}
