<?php

namespace Lunar\Shipping\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Pages\EditShippingExclusionList;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Pages\ListShippingExclusionLists;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\RelationManagers\ShippingExclusionRelationManager;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Schemas\ShippingExclusionListForm;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Tables\ShippingExclusionListTable;
use Lunar\Shipping\Models\Contracts\ShippingExclusionList;

class ShippingExclusionListResource extends BaseResource
{
    protected static ?string $model = ShippingExclusionList::class;

    protected static ?string $permission = 'shipping:manage';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel.shipping::shippingexclusionlist.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel.shipping::shippingexclusionlist.label_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-exclusion-lists');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel.shipping::plugin.navigation.group');
    }

    public static function form(Schema $schema): Schema
    {
        return ShippingExclusionListForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingExclusionListTable::configure($table);
    }

    protected static function getDefaultRelations(): array
    {
        return [
            ShippingExclusionRelationManager::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListShippingExclusionLists::route('/'),
            'edit' => EditShippingExclusionList::route('/{record}/edit'),
        ];
    }
}
