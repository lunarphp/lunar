<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;

use Awcodes\Shout\Components\Shout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
use Lunar\Shipping\Models\Contracts\ShippingMethod as ShippingMethodContract;
use Lunar\Shipping\Models\ShippingMethod;
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
        return $form->schema([
            Shout::make('')->content(
                function () {
                    $pricesIncTax = config('lunar.pricing.stored_inclusive_of_tax', false);

                    if ($pricesIncTax) {
                        return __('lunarpanel.shipping::relationmanagers.shipping_rates.notices.prices_inc_tax');
                    }

                    return __('lunarpanel.shipping::relationmanagers.shipping_rates.notices.prices_excl_tax');
                }
            ),
            Forms\Components\Select::make('shipping_method_id')
                ->label(
                    __('lunarpanel.shipping::relationmanagers.shipping_rates.form.shipping_method_id.label')
                )
                ->required()
                ->live()
                ->relationship(name: 'shippingMethod', titleAttribute: 'name')
                ->columnSpan(2),
            Forms\Components\TextInput::make('price')
                ->label(
                    __('lunarpanel.shipping::relationmanagers.shipping_rates.form.price.label')
                )
                ->numeric()
                ->required()
                ->columnSpan(2)
                ->afterStateHydrated(static function (Forms\Components\TextInput $component, ?Model $record = null): void {
                    if ($record) {
                        $basePrice = $record->basePrices->first();

                        $component->state(
                            $basePrice->price->decimal
                        );
                    }
                }),
            Forms\Components\Repeater::make('prices')
                ->label(
                    __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.label')
                )->schema([
                    Forms\Components\Select::make('customer_group_id')
                        ->label(
                            __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.customer_group_id.label')
                        )
                        ->options(
                            fn () => CustomerGroup::all()->pluck('name', 'id')
                        )->placeholder(
                            __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.customer_group_id.placeholder')
                        )->preload(),
                    Forms\Components\Select::make('currency_id')
                        ->label(
                            __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.currency_id.label')
                        )
                        ->options(
                            fn () => Currency::all()->pluck('name', 'id')
                        )->default(
                            Currency::getDefault()->id
                        )->required()->preload(),
                    Forms\Components\TextInput::make('price')
                        ->label(
                            __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.price.label')
                        )
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('min_quantity')
                        ->label(
                            function (Get $get) {
                                if (static::getShippingChargeBy($get('../../shipping_method_id')) == 'weight') {
                                    return __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.min_weight.label');
                                }

                                return __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.min_spend.label');
                            }
                        )
                        ->numeric()
                        ->required(),
                ])->afterStateHydrated(
                    static function (Forms\Components\Repeater $component, ?Model $record = null): void {
                        if ($record) {
                            $chargeBy = static::getShippingChargeBy($record->shippingMethod);
                            $currencies = Currency::all();

                            $component->state(
                                $record->priceBreaks->map(function ($price) use ($chargeBy, $currencies) {
                                    $currency = $currencies->first(fn ($currency) => $currency->id == $price->currency_id);

                                    return [
                                        'customer_group_id' => $price->customer_group_id,
                                        'price' => $price->price->decimal,
                                        'currency_id' => $price->currency_id,
                                        'min_quantity' => $chargeBy == 'cart_total' ? $price->min_quantity / $currency->factor : $price->min_quantity / 100,
                                    ];
                                })->toArray()
                            );
                        }
                    }
                )->columns(4),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('shippingMethod.name')
                ->label(
                    __('lunarpanel.shipping::relationmanagers.shipping_rates.table.shipping_method.label')
                ),
            TextColumn::make('basePrices.0')->formatStateUsing(
                fn ($state = null) => $state->price->formatted
            )->label(
                __('lunarpanel.shipping::relationmanagers.shipping_rates.table.price.label')
            ),
            TextColumn::make('price_breaks_count')
                ->label(
                    __('lunarpanel.shipping::relationmanagers.shipping_rates.table.price_breaks_count.label')
                )->counts('priceBreaks'),
        ])->headerActions([
            Tables\Actions\CreateAction::make()->label(
                __('lunarpanel.shipping::relationmanagers.shipping_rates.actions.create.label')
            )->action(function (Table $table, ?ShippingRate $shippingRate = null, array $data = []) {
                $relationship = $table->getRelationship();

                $record = new ShippingRate;
                $record->shipping_method_id = $data['shipping_method_id'];
                $relationship->save($record);

                static::saveShippingRate($record, $data);
            })->slideOver(),
        ])->actions([

            Tables\Actions\EditAction::make()->slideOver()->action(function (ShippingRate $shippingRate, array $data) {
                static::saveShippingRate($shippingRate, $data);
            }),
            Tables\Actions\DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    private static function getShippingChargeBy(ShippingMethodContract|int|null $method): string
    {
        if (blank($method)) {
            return 'cart_total';
        }

        if (! $method instanceof ShippingMethodContract) {
            $method = ShippingMethod::find($method);
        }

        return ($method?->data['charge_by'] ?? null) ?? 'cart_total';
    }

    protected static function saveShippingRate(?ShippingRate $shippingRate = null, array $data = []): void
    {
        $currency = Currency::getDefault();

        $basePrice = $shippingRate->basePrices->first() ?: new Price;

        $basePrice->price = (int) ($data['price'] * $currency->factor);
        $basePrice->priceable_type = $shippingRate->getMorphClass();
        $basePrice->currency_id = $currency->id;
        $basePrice->priceable_id = $shippingRate->id;
        $basePrice->customer_group_id = null;
        $basePrice->save();

        $shippingRate->priceBreaks()->delete();

        $currencies = Currency::all();
        $chargeBy = static::getShippingChargeBy($shippingRate->shippingMethod);

        $tiers = collect($data['prices'] ?? [])->map(
            function ($price) use ($chargeBy, $currencies) {
                $currency = $currencies->first(fn ($currency) => $currency->id == $price['currency_id']);

                if ($chargeBy == 'cart_total') {
                    $price['min_quantity'] = (int) ($price['min_quantity'] * $currency->factor);
                } else {
                    $price['min_quantity'] = (int) ($price['min_quantity'] * 100);
                }

                $price['price'] = (int) ($price['price'] * $currency->factor);

                return $price;
            }
        );

        $shippingRate->prices()->createMany($tiers->toArray());
    }
}
