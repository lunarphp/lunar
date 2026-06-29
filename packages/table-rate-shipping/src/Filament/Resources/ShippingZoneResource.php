<?php

namespace Lunar\Shipping\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages\EditShippingZone;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages\ListShippingZones;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages\ManageShippingExclusions;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages\ManageShippingRates;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Schemas\ShippingZoneForm;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Tables\ShippingZoneTable;
use Lunar\Shipping\Models\Contracts\ShippingZone;

class ShippingZoneResource extends BaseResource
{
    protected static ?string $model = ShippingZone::class;

    protected static ?string $permission = 'shipping:manage';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel.shipping::shippingzone.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel.shipping::shippingzone.label_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-zones');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel.shipping::plugin.navigation.group');
    }

    public static function form(Schema $schema): Schema
    {
        return ShippingZoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingZoneTable::configure($table);
    }

    protected static function getDefaultSubNavigation(): array
    {
        return [
            EditShippingZone::class,
            ManageShippingRates::class,
            ManageShippingExclusions::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListShippingZones::route('/'),
            'edit' => EditShippingZone::route('/{record}/edit'),
            'rates' => ManageShippingRates::route('/{record}/rates'),
            'exclusions' => ManageShippingExclusions::route('/{record}/exclusions'),
        ];
    }
}
