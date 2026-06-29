<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Clusters\Taxes;
use Lunar\Admin\Filament\Resources\TaxRateResource\Pages\CreateTaxRate;
use Lunar\Admin\Filament\Resources\TaxRateResource\Pages\EditTaxRate;
use Lunar\Admin\Filament\Resources\TaxRateResource\Pages\ListTaxRates;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\TaxRate;
use Lunar\Filament\RelationManagers\TaxRate\TaxRateAmountRelationManager;
use Lunar\Filament\Schemas\TaxRate\TaxRateForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\TaxRate\TaxRateTable;

class TaxRateResource extends BaseResource
{
    protected static ?string $cluster = Taxes::class;

    protected static ?string $permission = 'settings:core';

    protected static ?string $model = TaxRate::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::taxrate.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::taxrate.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(TaxRateForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(TaxRateTable::class, $table);
    }

    protected static function getDefaultRelations(): array
    {
        return [
            TaxRateAmountRelationManager::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListTaxRates::route('/'),
            'edit' => EditTaxRate::route('/{record}/edit'),
            'create' => CreateTaxRate::route('/create'),
        ];
    }
}
