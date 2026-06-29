<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages\CreateCurrency;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages\EditCurrency;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages\ListCurrencies;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Currency;
use Lunar\Filament\Schemas\Currency\CurrencyForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Currency\CurrencyTable;

class CurrencyResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = Currency::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::currency.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::currency.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::currencies');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(CurrencyForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(CurrencyTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}
