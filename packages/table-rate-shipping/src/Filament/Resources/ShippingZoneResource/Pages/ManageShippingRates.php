<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource;

class ManageShippingRates extends ManageRelatedRecords
{
    protected static string $resource = ShippingZoneResource::class;

    protected static string $relationship = 'rates';

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel.shipping::relationmanagers.shipping_rates.title_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-rates');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel.shipping::relationmanagers.shipping_rates.title_plural');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('shipping_method_id')->relationship(name: 'shippingMethod', titleAttribute: 'name')->columnSpan(2),
            TextInput::make('base_price')->numeric()->required()->columnSpan(2),
            Repeater::make('prices'),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('shippingMethod.name'),
        ])->headerActions([
            Tables\Actions\CreateAction::make()->label(
                __('lunarpanel.shipping::relationmanagers.shipping_rates.actions.create.label')
            )->slideOver(),
        ])->actions([
            Tables\Actions\EditAction::make()->slideOver(),
        ]);
    }
}
