<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager;

class ManageProductMedia extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'media';

    public function getTitle(): string
    {
        return __('lunarpanel::product.pages.media.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::media');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.media.label');
    }

    public function getRelationManagers(): array
    {
        $mediaCollections = $this->getOwnerRecord()->getRegisteredMediaCollections();

        $relationManagers = [];

        foreach ($mediaCollections as $mediaCollection) {
            $relationManagers[] = MediaRelationManager::make([
                'mediaCollection' => $mediaCollection->name,
            ]);
        }

        return [
            RelationGroup::make('Media', $relationManagers),
        ];
    }
}
