<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\RegionResource\Pages\CreateRegion;
use Lunar\Admin\Filament\Resources\RegionResource\Pages\EditRegion;
use Lunar\Admin\Filament\Resources\RegionResource\Pages\ListRegions;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Region;
use Lunar\Filament\Schemas\Region\RegionForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Region\RegionTable;

class RegionResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = Region::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::region.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::region.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::channels');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(RegionForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(RegionTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListRegions::route('/'),
            'create' => CreateRegion::route('/create'),
            'edit' => EditRegion::route('/{record}/edit'),
        ];
    }
}
