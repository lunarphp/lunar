<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\PromotionResource\Pages\CreatePromotion;
use Lunar\Admin\Filament\Resources\PromotionResource\Pages\EditPromotion;
use Lunar\Admin\Filament\Resources\PromotionResource\Pages\ListPromotions;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Promotion;
use Lunar\Filament\RelationManagers\Promotion\DiscountsRelationManager;
use Lunar\Filament\Schemas\Promotion\PromotionForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Promotion\PromotionTable;

class PromotionResource extends BaseResource
{
    protected static ?string $permission = 'sales:manage-discounts';

    protected static ?string $model = Promotion::class;

    protected static ?int $navigationSort = 4;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel::promotion.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::promotion.plural_label');
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
        return Resolver::form(PromotionForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(PromotionTable::class, $table);
    }

    protected static function getDefaultRelations(): array
    {
        return [
            DiscountsRelationManager::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListPromotions::route('/'),
            'create' => CreatePromotion::route('/create'),
            'edit' => EditPromotion::route('/{record}/edit'),
        ];
    }
}
