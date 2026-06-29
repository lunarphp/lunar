<?php

namespace Lunar\Shipping\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages\EditShippingMethod;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages\ListShippingMethod;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages\ManageShippingMethodAvailability;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Schemas\ShippingMethodForm;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Tables\ShippingMethodTable;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Widgets\AvailabilityScheduleWidget;
use Lunar\Shipping\Models\Contracts\ShippingMethod;

class ShippingMethodResource extends BaseResource
{
    protected static ?string $model = ShippingMethod::class;

    protected static ?string $permission = 'shipping:manage';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel.shipping::shippingmethod.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel.shipping::shippingmethod.label_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-methods');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel.shipping::plugin.navigation.group');
    }

    public static function form(Schema $schema): Schema
    {
        return ShippingMethodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingMethodTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            AvailabilityScheduleWidget::class,
        ];
    }

    protected static function getDefaultSubNavigation(): array
    {
        return [
            EditShippingMethod::class,
            ManageShippingMethodAvailability::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListShippingMethod::route('/'),
            'edit' => EditShippingMethod::route('/{record}/edit'),
            'availability' => ManageShippingMethodAvailability::route('/{record}/availability'),
        ];
    }
}
