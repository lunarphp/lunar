<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Collection;

class CollectionResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-collections';

    protected static ?string $model = Collection::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

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

    public static function getCollectionBreadcrumbs(Collection $collection): array
    {
        $crumbs = [
            CollectionGroupResource::getUrl('edit', [
                'record' => $collection->group,
            ]) => $collection->group->name,
        ];

        foreach ($collection->ancestors as $childCollection) {
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

    public static function getDefaultForm(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                static::getAttributeDataFormComponent(),
            ])
            ->columns(1);
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make()->statePath('attribute_data');
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

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditCollection::class,
            Pages\ManageCollectionChildren::class,
            Pages\ManageCollectionAvailability::class,
            Pages\ManageCollectionMedia::class,
            Pages\ManageCollectionUrls::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'availability' => Pages\ManageCollectionAvailability::route('/{record}/availability'),
            'children' => Pages\ManageCollectionChildren::route('/{record}/children'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
            'media' => Pages\ManageCollectionMedia::route('/{record}/media'),
            'urls' => Pages\ManageCollectionUrls::route('/{record}/urls'),
        ];
    }
}
