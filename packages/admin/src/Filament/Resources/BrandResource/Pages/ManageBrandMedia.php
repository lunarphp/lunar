<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager;

class ManageBrandMedia extends BaseManageRelatedRecords
{
    protected static string $resource = BrandResource::class;

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
