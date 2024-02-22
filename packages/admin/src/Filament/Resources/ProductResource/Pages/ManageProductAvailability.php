<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupRelationManager;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;

class ManageProductAvailability extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'channels';

    public function getTitle(): string
    {

        return __('lunarpanel::product.pages.availability.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::availability');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.availability.label');
    }

    public function getRelationManagers(): array
    {
        return [
            RelationGroup::make('Availability', [
                ChannelRelationManager::class,
                CustomerGroupRelationManager::class,
            ]),
        ];
    }
}
