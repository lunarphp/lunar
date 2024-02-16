<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupRelationManager;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;

class ManageDiscountAvailability extends ManageRelatedRecords
{
    protected static string $resource = DiscountResource::class;

    protected static string $relationship = 'channels';

    public function getTitle(): string
    {
        return __('lunarpanel::discount.pages.availability.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::availability');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::discount.pages.availability.label');
    }

    public function getRelationManagers(): array
    {
        return [
            RelationGroup::make('Availability', [
                ChannelRelationManager::class,
                CustomerGroupRelationManager::make([
                    'pivots' => [
                        'enabled',
                        'visible',
                    ],
                ]),
            ]),
        ];
    }
}
