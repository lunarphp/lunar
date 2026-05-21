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
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\BrandLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\CollectionConditionRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\CollectionLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\CustomerLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductConditionRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductRewardRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductVariantLimitationRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\Schemas\DiscountForm;
use Lunar\Admin\Filament\Resources\DiscountResource\Tables\DiscountTable;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Discount as DiscountContract;

class DiscountResource extends BaseResource
{
    protected static ?string $permission = 'sales:manage-discounts';

    protected static ?string $model = DiscountContract::class;

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
        return DiscountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscountTable::configure($table);
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
