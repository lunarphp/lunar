<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Awcodes\Shout\Components\Shout;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
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

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Shout::make('pricing_notice')->content(
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
            Group::make(static function (): array {
                $currencies = Currency::whereEnabled(true)
                    ->orderByDesc('default')
                    ->orderBy('name')
                    ->get();

                return $currencies->map(fn ($currency) => Forms\Components\TextInput::make("base_prices.{$currency->id}")
                    ->label($currency->name)
                    ->numeric()
                    ->required($currency->default)
                    ->afterStateHydrated(static function (Forms\Components\TextInput $component, ?Model $record = null) use ($currency): void {
                        if ($record) {
                            if ($basePrice = $record->basePrices->first(fn ($p) => $p->currency_id == $currency->id)) {
                                $component->state($basePrice->price->decimal);
                            }
                        }
                    })
                )->toArray();
            })->columns(2)->columnSpan(2),
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
                        ->label(fn (Get $get) => static::isWeightCharge($get)
                            ? __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.min_weight.label')
                            : __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.min_spend.label')
                        )
                        ->helperText(fn (Get $get) => static::isWeightCharge($get)
                            ? __('lunarpanel.shipping::relationmanagers.shipping_rates.form.prices.repeater.min_weight.helper_text', [
                                'unit' => static::getShippingWeightUnit($get('../../shipping_method_id')),
                            ])
                            : null
                        )
                        // Unit symbol — intentionally not translated.
                        ->suffix(fn (Get $get) => static::isWeightCharge($get)
                            ? static::getShippingWeightUnit($get('../../shipping_method_id'))
                            : null
                        )
                        ->numeric()
                        // Weight tiers are stored as raw integers in the method's
                        // weight unit — reject decimals instead of truncating them.
                        ->rules(fn (Get $get) => static::isWeightCharge($get) ? ['integer'] : [])
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
                                        'min_quantity' => $chargeBy == 'cart_total' ? $price->min_quantity / $currency->factor : $price->min_quantity,
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
        $baseCurrency = Currency::getDefault();

        return $table->columns([
            BadgeableColumn::make('shippingMethod.name')
                ->separator('')
                ->suffixBadges([
                    Badge::make('default')
                        ->label(__('lunarpanel.shipping::relationmanagers.shipping_rates.table.shipping_method.disabled'))
                        ->color('warning')
                        ->visible(fn (Model $record) => ! $record->enabled),
                ])
                ->label(__('lunarpanel.shipping::relationmanagers.shipping_rates.table.shipping_method.label')),
            TextColumn::make('shippingMethod.id')->formatStateUsing(
                fn (Model $record) => $record->basePrices->first(fn ($p) => $p->currency_id == $baseCurrency->id)?->price->formatted ?? '-',
            )->label(
                __('lunarpanel.shipping::relationmanagers.shipping_rates.table.price.label')
            ),
            TextColumn::make('price_breaks_count')
                ->label(
                    __('lunarpanel.shipping::relationmanagers.shipping_rates.table.price_breaks_count.label')
                )->counts('priceBreaks'),
        ])->headerActions([
            CreateAction::make()->label(
                __('lunarpanel.shipping::relationmanagers.shipping_rates.actions.create.label')
            )->action(function (Table $table, ?ShippingRate $shippingRate = null, array $data = []) {
                $relationship = $table->getRelationship();

                $record = new ShippingRate;
                $record->shipping_method_id = $data['shipping_method_id'];
                $relationship->save($record);

                static::saveShippingRate($record, $data);
            })->slideOver(),
        ])->actions([

            EditAction::make()->slideOver()->action(function (ShippingRate $shippingRate, array $data) {
                static::saveShippingRate($shippingRate, $data);
            }),
            DeleteAction::make()->requiresConfirmation(),
            Action::make('disable')->color('warning')->action(function (ShippingRate $shippingRate) {
                $shippingRate->updateQuietly([
                    'enabled' => false,
                ]);
            })->hidden(
                fn (ShippingRate $shippingRate) => ! $shippingRate->enabled
            ),
            Action::make('enable')->color('success')->action(function (ShippingRate $shippingRate) {
                $shippingRate->updateQuietly([
                    'enabled' => true,
                ]);
            })->hidden(
                fn (ShippingRate $shippingRate) => (bool) $shippingRate->enabled
            ),

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

    private static function isWeightCharge(Get $get): bool
    {
        return static::getShippingChargeBy($get('../../shipping_method_id')) === 'weight';
    }

    private static function getShippingWeightUnit(ShippingMethodContract|int|null $method): string
    {
        if (blank($method)) {
            return 'kg';
        }

        if (! $method instanceof ShippingMethodContract) {
            $method = ShippingMethod::find($method);
        }

        return $method?->weight_unit ?: 'kg';
    }

    protected static function saveShippingRate(?ShippingRate $shippingRate = null, array $data = []): void
    {
        $shippingRate->basePrices()->delete();

        $enabledCurrencies = Currency::whereEnabled(true)->get()->keyBy('id');

        foreach ($data['base_prices'] ?? [] as $currencyId => $priceValue) {
            if ($priceValue === null || $priceValue === '') {
                continue;
            }

            if (! $currency = $enabledCurrencies->get($currencyId)) {
                continue;
            }

            $shippingRate->prices()->create([
                'price' => (int) round($priceValue * $currency->factor),
                'currency_id' => $currency->id,
                'customer_group_id' => null,
                'min_quantity' => 1,
            ]);
        }

        $shippingRate->priceBreaks()->delete();

        $currencies = Currency::all();
        $chargeBy = static::getShippingChargeBy($shippingRate->shippingMethod);

        $tiers = collect($data['prices'] ?? [])->map(
            function ($price) use ($chargeBy, $currencies) {
                $currency = $currencies->first(fn ($currency) => $currency->id == $price['currency_id']);

                if ($chargeBy == 'cart_total') {
                    $price['min_quantity'] = (int) ($price['min_quantity'] * $currency->factor);
                } else {
                    $price['min_quantity'] = (int) $price['min_quantity'];
                }

                $price['price'] = (int) ($price['price'] * $currency->factor);

                return $price;
            }
        );

        $shippingRate->prices()->createMany($tiers->toArray());
    }
}
