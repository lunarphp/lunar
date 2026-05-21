<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Clusters\Taxes;
use Lunar\Admin\Filament\Resources\TaxClassResource\Pages\CreateTaxClass;
use Lunar\Admin\Filament\Resources\TaxClassResource\Pages\EditTaxClass;
use Lunar\Admin\Filament\Resources\TaxClassResource\Pages\ListTaxClasses;
use Lunar\Admin\Filament\Resources\TaxClassResource\Schemas\TaxClassForm;
use Lunar\Admin\Filament\Resources\TaxClassResource\Tables\TaxClassTable;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\TaxClass as TaxClassContract;

class TaxClassResource extends BaseResource
{
    protected static ?string $cluster = Taxes::class;

    protected static ?string $permission = 'settings:core';

    protected static ?string $model = TaxClassContract::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::taxclass.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::taxclass.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::tax');
    }

    public static function form(Schema $schema): Schema
    {
        return TaxClassForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxClassTable::configure($table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListTaxClasses::route('/'),
            'create' => CreateTaxClass::route('/create'),
            'edit' => EditTaxClass::route('/{record}/edit'),
        ];
    }
}
