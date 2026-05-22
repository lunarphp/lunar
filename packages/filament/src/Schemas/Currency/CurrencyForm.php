<?php

namespace Lunar\Filament\Schemas\Currency;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Support\Concerns\CallsHooks;

class CurrencyForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make('details')
                    ->schema(static::getMainComponents())
                    ->heading()
                    ->columns(),
            ]),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getCodeComponent(),
            static::getExchangeRateComponent(),
            static::getDecimalPlacesComponent(),
            static::getEnabledComponent(),
            static::getDefaultComponent(),
            static::getSyncPricesComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::currency.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getCodeComponent(): Component
    {
        return TextInput::make('code')
            ->label(__('lunar-filament::currency.form.code.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->minLength(3)
            ->maxLength(3);
    }

    public static function getExchangeRateComponent(): Component
    {
        return TextInput::make('exchange_rate')
            ->label(__('lunar-filament::currency.form.exchange_rate.label'))
            ->numeric()
            ->required();
    }

    public static function getDecimalPlacesComponent(): Component
    {
        return TextInput::make('decimal_places')
            ->label(__('lunar-filament::currency.form.decimal_places.label'))
            ->numeric()
            ->required();
    }

    public static function getEnabledComponent(): Component
    {
        return Toggle::make('enabled')
            ->label(__('lunar-filament::currency.form.enabled.label'));
    }

    public static function getDefaultComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunar-filament::currency.form.default.label'));
    }

    public static function getSyncPricesComponent(): Component
    {
        return Toggle::make('sync_prices')
            ->label(__('lunar-filament::currency.form.sync_prices.label'))
            ->helperText(__('lunar-filament::currency.form.sync_prices.helper_text'))
            ->hidden(fn (?Model $record) => (bool) $record?->default)
            ->default(true);
    }
}
