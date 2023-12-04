<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Currency;

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

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getCodeFormComponent(),
            static::getExchangeRateFormComponent(),
            static::getDecimalPlacesFormComponent(),
            static::getEnabledFormComponent(),
            static::getDefaultFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::currency.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getCodeFormComponent(): Component
    {
        return Forms\Components\TextInput::make('code')
            ->label(__('lunarpanel::currency.form.code.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->minLength(3)
            ->maxLength(3);
    }

    protected static function getExchangeRateFormComponent(): Component
    {
        return Forms\Components\TextInput::make('exchange_rate')
            ->label(__('lunarpanel::currency.form.exchange_rate.label'))
            ->numeric()
            ->required();
    }

    protected static function getDecimalPlacesFormComponent(): Component
    {
        return Forms\Components\TextInput::make('decimal_places')
            ->label(__('lunarpanel::currency.form.decimal_places.label'))
            ->numeric()
            ->required();
    }

    protected static function getEnabledFormComponent(): Component
    {
        return Forms\Components\Toggle::make('enabled')
            ->label(__('lunarpanel::currency.form.enabled.label'));
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Forms\Components\Toggle::make('default')
            ->label(__('lunarpanel::currency.form.default.label'));
    }

    protected static function getDefaultTable(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::currency.table.name.label')),
            Tables\Columns\TextColumn::make('code')
                ->label(__('lunarpanel::currency.table.code.label')),
            Tables\Columns\TextColumn::make('exchange_rate')
                ->label(__('lunarpanel::currency.table.exchange_rate.label')),
            Tables\Columns\TextColumn::make('decimal_places')
                ->label(__('lunarpanel::currency.table.decimal_places.label')),
            Tables\Columns\BooleanColumn::make('enabled')
                ->label(__('lunarpanel::currency.table.enabled.label')),
            Tables\Columns\BooleanColumn::make('default')
                ->label(__('lunarpanel::currency.table.default.label')),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
