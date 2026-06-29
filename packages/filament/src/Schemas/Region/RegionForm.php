<?php

namespace Lunar\Filament\Schemas\Region;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Lunar\Filament\Forms\Components\ChannelSelect;
use Lunar\Filament\Forms\Components\CountrySelect;
use Lunar\Filament\Forms\Components\CurrencySelect;
use Lunar\Filament\Forms\Components\LanguageSelect;
use Lunar\Filament\Forms\Components\TaxZoneSelect;
use Lunar\Filament\Support\Concerns\CallsHooks;

class RegionForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make()->schema(static::getMainComponents()),
            ]),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getHandleComponent(),
            static::getChannelComponent(),
            static::getCurrencyComponent(),
            static::getLanguageComponent(),
            static::getTaxZoneComponent(),
            static::getPricesIncTaxComponent(),
            static::getCountriesComponent(),
            static::getDefaultComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::region.form.name.label'))
            ->required()
            ->maxLength(255)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state));
            })
            ->live(onBlur: true)
            ->autofocus();
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunar-filament::region.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255);
    }

    public static function getChannelComponent(): Component
    {
        return ChannelSelect::make('channel_id')
            ->label(__('lunar-filament::region.form.channel.label'))
            ->required();
    }

    public static function getCurrencyComponent(): Component
    {
        return CurrencySelect::make('currency_id')
            ->label(__('lunar-filament::region.form.currency.label'))
            ->required();
    }

    public static function getLanguageComponent(): Component
    {
        return LanguageSelect::make('language_id')
            ->label(__('lunar-filament::region.form.language.label'))
            ->required();
    }

    public static function getTaxZoneComponent(): Component
    {
        return TaxZoneSelect::make('tax_zone_id')
            ->label(__('lunar-filament::region.form.tax_zone.label'));
    }

    public static function getPricesIncTaxComponent(): Component
    {
        return Select::make('prices_inc_tax')
            ->label(__('lunar-filament::region.form.prices_inc_tax.label'))
            ->options([
                'inherit' => __('lunar-filament::region.form.prices_inc_tax.options.inherit'),
                'inclusive' => __('lunar-filament::region.form.prices_inc_tax.options.inclusive'),
                'exclusive' => __('lunar-filament::region.form.prices_inc_tax.options.exclusive'),
            ])
            ->default('inherit')
            ->selectablePlaceholder(false)
            ->formatStateUsing(fn ($state): string => match (true) {
                $state === true => 'inclusive',
                $state === false => 'exclusive',
                default => 'inherit',
            })
            ->dehydrateStateUsing(fn ($state): ?bool => match ($state) {
                'inclusive' => true,
                'exclusive' => false,
                default => null,
            });
    }

    public static function getCountriesComponent(): Component
    {
        return CountrySelect::make('countries')
            ->label(__('lunar-filament::region.form.countries.label'))
            ->multiple()
            ->relationship('countries', 'name');
    }

    public static function getDefaultComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunar-filament::region.form.default.label'));
    }
}
