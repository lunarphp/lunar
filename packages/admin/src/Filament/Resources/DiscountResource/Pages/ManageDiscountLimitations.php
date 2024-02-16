<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class ManageDiscountLimitations extends BaseEditRecord
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

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getRelationManagers(): array
    {
        return [
            RelationGroup::make('Limitations', [
                DiscountResource\RelationManagers\CollectionLimitationRelationManager::class,
                DiscountResource\RelationManagers\BrandLimitationRelationManager::class,
                DiscountResource\RelationManagers\ProductLimitationRelationManager::class,
                DiscountResource\RelationManagers\ProductVariantLimitationRelationManager::class,
            ]),

        ];
    }
}
