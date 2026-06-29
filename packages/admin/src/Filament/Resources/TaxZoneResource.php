<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Clusters\Taxes;
use Lunar\Admin\Filament\Resources\TaxZoneResource\Pages\CreateTaxZone;
use Lunar\Admin\Filament\Resources\TaxZoneResource\Pages\EditTaxZone;
use Lunar\Admin\Filament\Resources\TaxZoneResource\Pages\ListTaxZones;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\TaxZone;
use Lunar\Filament\Schemas\TaxZone\TaxZoneForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\TaxZone\TaxZoneTable;

class TaxZoneResource extends BaseResource
{
    protected static ?string $cluster = Taxes::class;

    protected static ?string $permission = 'settings:core';

    protected static ?string $model = TaxZone::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::taxzone.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::taxzone.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(TaxZoneForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(TaxZoneTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListTaxZones::route('/'),
            'edit' => EditTaxZone::route('/{record}/edit'),
            'create' => CreateTaxZone::route('/create'),
        ];
    }
}
