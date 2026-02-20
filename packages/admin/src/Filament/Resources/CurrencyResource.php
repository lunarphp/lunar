<?php

namespace Lunar\Admin\Filament\Resources;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages\CreateCurrency;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages\EditCurrency;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages\ListCurrencies;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Currency as CurrencyContract;

class CurrencyResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = CurrencyContract::class;

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

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('details')->schema(
                static::getMainFormComponents()
            )->heading()->columns(),
        ]);
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
            static::getSyncPricesFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::currency.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getCodeFormComponent(): Component
    {
        return TextInput::make('code')
            ->label(__('lunarpanel::currency.form.code.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->minLength(3)
            ->maxLength(3);
    }

    protected static function getExchangeRateFormComponent(): Component
    {
        return TextInput::make('exchange_rate')
            ->label(__('lunarpanel::currency.form.exchange_rate.label'))
            ->numeric()
            ->required();
    }

    protected static function getDecimalPlacesFormComponent(): Component
    {
        return TextInput::make('decimal_places')
            ->label(__('lunarpanel::currency.form.decimal_places.label'))
            ->numeric()
            ->required();
    }

    protected static function getEnabledFormComponent(): Component
    {
        return Toggle::make('enabled')
            ->label(__('lunarpanel::currency.form.enabled.label'));
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunarpanel::currency.form.default.label'));
    }

    protected static function getSyncPricesFormComponent(): Component
    {
        return Toggle::make('sync_prices')
            ->label(__('lunarpanel::currency.form.sync_prices.label'))
            ->helperText(__('lunarpanel::currency.form.sync_prices.helper_text'))
            ->hidden(
                fn (?Model $record) => (bool) $record?->default
            )
            ->default(true);
    }

    protected static function getDefaultTable(Table $table): Table
    {
        return $table->columns([
            BadgeableColumn::make('name')
                ->separator('')
                ->suffixBadges([
                    Badge::make('default')
                        ->label(__('lunarpanel::currency.table.default.label'))
                        ->color('gray')
                        ->visible(fn (Model $record) => $record->default),
                ])
                ->label(__('lunarpanel::currency.table.name.label')),
            TextColumn::make('code')
                ->label(__('lunarpanel::currency.table.code.label')),
            TextColumn::make('exchange_rate')
                ->label(__('lunarpanel::currency.table.exchange_rate.label')),
            TextColumn::make('decimal_places')
                ->label(__('lunarpanel::currency.table.decimal_places.label')),
            IconColumn::make('enabled')
                ->boolean()
                ->label(__('lunarpanel::currency.table.enabled.label')),
            IconColumn::make('sync_prices')
                ->boolean()
                ->label(__('lunarpanel::currency.table.sync_prices.label')),
        ]);
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}
