<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\EditDiscount;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\ListDiscounts;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\ManageDiscountAvailability;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\ManageDiscountLimitations;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Discount;
use Lunar\Filament\RelationManagers\Discount\BrandLimitationRelationManager;
use Lunar\Filament\RelationManagers\Discount\CollectionConditionRelationManager;
use Lunar\Filament\RelationManagers\Discount\CollectionLimitationRelationManager;
use Lunar\Filament\RelationManagers\Discount\CustomerLimitationRelationManager;
use Lunar\Filament\RelationManagers\Discount\ProductConditionRelationManager;
use Lunar\Filament\RelationManagers\Discount\ProductLimitationRelationManager;
use Lunar\Filament\RelationManagers\Discount\ProductRewardRelationManager;
use Lunar\Filament\RelationManagers\Discount\ProductVariantLimitationRelationManager;
use Lunar\Filament\Schemas\Discount\DiscountForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Discount\DiscountTable;

class DiscountResource extends BaseResource
{
    protected static ?string $permission = 'sales:manage-discounts';

    protected static ?string $model = Discount::class;

    protected static ?int $navigationSort = 3;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel::discount.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::discount.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::discounts');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.sales');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(DiscountForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(DiscountTable::class, $table);
    }

    protected static function getDefaultSubNavigation(): array
    {
        return [
            EditDiscount::class,
            ManageDiscountAvailability::class,
            ManageDiscountLimitations::class,
        ];
    }

    protected static function getDefaultRelations(): array
    {
        return [
            CollectionLimitationRelationManager::class,
            BrandLimitationRelationManager::class,
            ProductLimitationRelationManager::class,
            ProductVariantLimitationRelationManager::class,
            CustomerLimitationRelationManager::class,
            ProductRewardRelationManager::class,
            ProductConditionRelationManager::class,
            CollectionConditionRelationManager::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListDiscounts::route('/'),
            'edit' => EditDiscount::route('/{record}'),
            'limitations' => ManageDiscountLimitations::route('/{record}/limitations'),
            'availability' => ManageDiscountAvailability::route('/{record}/availability'),
        ];
    }
}
