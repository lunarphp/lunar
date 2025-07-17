<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\BrandLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\CollectionLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductVariantLimitationRelationManager;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class ManageBuyXGetYDiscount extends BaseEditRecord
{
    protected static string $resource = DiscountResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::discount.pages.limitations.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::discount.pages.limitations.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::discount-limitations');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getRelationManagers(): array
    {
        return [
            RelationGroup::make('Limitations', [
                CollectionLimitationRelationManager::class,
                BrandLimitationRelationManager::class,
                ProductLimitationRelationManager::class,
                ProductVariantLimitationRelationManager::class,
            ]),

        ];
    }
}
