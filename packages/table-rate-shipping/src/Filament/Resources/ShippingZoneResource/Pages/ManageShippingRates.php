<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource;
use Lunar\Shipping\Models\ShippingRate;

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
        $priceInputs = [];

        return $form->schema([
            Forms\Components\Select::make('shipping_method_id')->relationship(name: 'shippingMethod', titleAttribute: 'name')->columnSpan(2),
            Forms\Components\TextInput::make('base_price')
                ->numeric()
                ->required()
                ->columnSpan(2)
                ->afterStateHydrated(static function (Forms\Components\TextInput $component, Model $record): void {
                    $basePrice = $record->basePrices->first();

                    $component->state(
                        $basePrice->price->decimal
                    );
                }),
            Forms\Components\Repeater::make('prices')->schema([
                Forms\Components\Select::make('customer_group_id')
                    ->options(
                        fn () => CustomerGroup::all()->pluck('name', 'id')
                    )->preload(),
                Forms\Components\Select::make('currency_id')
                    ->options(
                        fn () => Currency::all()->pluck('name', 'id')
                    )->default(
                        Currency::getDefault()->id
                    )->required()->preload(),
                Forms\Components\TextInput::make('tier')
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->numeric(),
            ])->afterStateHydrated(
                static function (Forms\Components\Repeater $component, Model $record): void {
                    $component->state(
                        $record->tieredPrices->map(function ($price) {
                            return [
                                'customer_group_id' => $price->customer_group_id,
                                'price' => $price->price->decimal,
                                'currency_id' => $price->currency_id,
                                'tier' => $price->tier,
                            ];
                        })->toArray()
                    );
                }
            )->columns(4),
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
            Tables\Actions\EditAction::make()->slideOver()->action(function (ShippingRate $shippingRate, array $data) {
                $currency = Currency::getDefault();

                $basePrice = $shippingRate->basePrices->first() ?: new Price;

                $basePrice->price = (int) ($data['base_price'] * $currency->factor);
                $basePrice->priceable_type = get_class($shippingRate);
                $basePrice->currency_id = $currency->id;
                $basePrice->priceable_id = $shippingRate->id;
                $basePrice->customer_group_id = null;
                $basePrice->save();

                $shippingRate->tieredPrices()->delete();

                $shippingRate->prices()->createMany($data['prices'] ?? []);
            }),

        ]);
    }
}
