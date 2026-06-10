<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\LocationResource\Pages\CreateLocation;
use Lunar\Admin\Filament\Resources\LocationResource\Pages\EditLocation;
use Lunar\Admin\Filament\Resources\LocationResource\Pages\ListLocations;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Location as LocationContract;
use Lunar\Filament\Schemas\Location\LocationForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Location\LocationTable;

class LocationResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = LocationContract::class;

    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return __('lunarpanel::location.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::location.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::locations');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(LocationForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(LocationTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListLocations::route('/'),
            'create' => CreateLocation::route('/create'),
            'edit' => EditLocation::route('/{record}/edit'),
        ];
    }
}
