<?php

namespace Lunar\Admin\Support\Concerns\Products;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Filament\Schemas\ProductVariant\ProductVariantForm;

trait ManagesProductPricing
{
    public ?string $tax_class_id = '';

    public ?string $tax_ref = '';

    public array $basePrices = [];

    public array $comparisonPrices = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $variant = $this->getOwnerRecord();

        $this->tax_class_id = $variant->tax_class_id;
        $this->tax_ref = $variant->tax_ref;
    }

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::relationmanagers.pricing.title');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data = $this->callLunarHook('beforeUpdate', $data, $record);

        $variant = $this->getOwnerRecord();

        // Merge form-submitted values (value, list_price) back into
        // the full basePrices property which contains metadata like
        // id, currency_id, factor, etc. that aren't form fields.
        $formPrices = $data['basePrices'] ?? [];
        $prices = collect($this->basePrices)->map(function (array $price, int $index) use ($formPrices): array {
            if (isset($formPrices[$index])) {
                return array_merge($price, $formPrices[$index]);
            }

            return $price;
        });
        unset($data['basePrices']);
        $variant->update($data);

        $currencies = Currency::whereIn('id', $prices->pluck('currency_id')->filter()->unique())->get()->keyBy('id');

        $prices->filter(
            fn ($price) => ! ($price['id'] ?? null) && isset($price['value']) && isset($price['currency_id'])
        )->each(function ($price) use ($variant, $currencies) {
            $currency = $currencies->get($price['currency_id']);

            $variant->prices()->create([
                'currency_id' => $price['currency_id'],
                'price' => PriceCalculator::toMinor((float) $price['value'], $currency),
                'list_price' => PriceCalculator::toMinor((float) ($price['list_price'] ?? 0), $currency),
                'min_quantity' => 1,
                'customer_group_id' => null,
            ]);
        });

        $prices->filter(
            fn ($price) => ($price['id'] ?? null) && isset($price['value']) && ($price['value'] != $price['original_value'] || $price['list_price'] != $price['original_list_price'])
        )->each(function ($price) use ($currencies) {
            $currency = $currencies->get($price['currency_id']);

            Price::find($price['id'])->update([
                'price' => PriceCalculator::toMinor((float) $price['value'], $currency),
                'list_price' => PriceCalculator::toMinor((float) $price['list_price'], $currency),
            ]);
        });

        $this->basePrices = $this->getBasePrices();

        $this->dispatch('refresh-relation-manager');

        return $this->callLunarHook('afterUpdate', $record, $data);
    }

    public function getBasePriceFormSection(): Section
    {
        return Section::make(
            __('lunarpanel::relationmanagers.pricing.form.basePrices.title')
        )
            ->schema(
                collect($this->basePrices)->map(function ($price, $index): Fieldset {
                    return Fieldset::make($price['label'])->schema([
                        TextInput::make('value')
                            ->label('')
                            ->statePath($index.'.value')
                            ->numeric()
                            ->label(
                                __('lunarpanel::relationmanagers.pricing.form.basePrices.form.price.label')
                            )
                            ->helperText(
                                __('lunarpanel::relationmanagers.pricing.form.basePrices.form.price.helper_text')
                            )
                            ->hintColor('warning')
                            ->extraInputAttributes([
                                'class' => '',
                            ])
                            ->hintIcon(function (Get $get, TextInput $component) use ($index, $price) {
                                if (! ($price['sync_prices'] ?? false) && $get('basePrices.'.$index.'.id', true)) {
                                    return null;
                                }

                                return FilamentIcon::resolve('lunar::info');
                            })->hintIconTooltip(function (Get $get, TextInput $component) use ($index, $price) {
                                if ($price['sync_prices'] ?? false) {
                                    return __('lunarpanel::relationmanagers.pricing.form.basePrices.form.price.sync_price');
                                }

                                if ($get('basePrices.'.$index.'.id', true)) {
                                    return null;
                                }

                                return __('lunarpanel::relationmanagers.pricing.form.basePrices.tooltip');
                            })
                            ->disabled(fn () => $price['sync_prices'] ?? false)
                            ->live(),
                        TextInput::make('list_price')
                            ->label('')
                            ->statePath($index.'.list_price')
                            ->numeric()
                            ->label(
                                __('lunarpanel::relationmanagers.pricing.form.basePrices.form.list_price.label')
                            )
                            ->helperText(
                                __('lunarpanel::relationmanagers.pricing.form.basePrices.form.list_price.helper_text')
                            )
                            ->hintColor('warning')
                            ->extraInputAttributes([
                                'class' => '',
                            ])
                            ->hintIcon(function (Get $get, TextInput $component) use ($index, $price) {
                                if (! ($price['sync_prices'] ?? false) && $get('basePrices.'.$index.'.id', true)) {
                                    return null;
                                }

                                return FilamentIcon::resolve('lunar::info');
                            })->hintIconTooltip(function (Get $get, TextInput $component) use ($index, $price) {
                                if ($price['sync_prices'] ?? false) {
                                    return __('lunarpanel::relationmanagers.pricing.form.basePrices.form.price.sync_price');
                                }

                                if ($get('basePrices.'.$index.'.id', true)) {
                                    return null;
                                }

                                return __('lunarpanel::relationmanagers.pricing.form.basePrices.tooltip');
                            })
                            ->disabled(fn () => $price['sync_prices'] ?? false)
                            ->live(),
                    ])->columns(2);
                })->toArray()
            )->statePath('basePrices')->columns(1);
    }

    public function form(Schema $schema): Schema
    {
        if (! count($this->basePrices)) {
            $this->basePrices = $this->getBasePrices();
        }

        $schema->components([
            Section::make()->schema([
                Group::make([
                    ProductVariantForm::getTaxClassIdComponent(),
                    ProductVariantForm::getTaxRefComponent(),
                ])->columns(2),
            ]),
            $this->getBasePriceFormSection(),
        ])->statePath('');

        $this->callLunarHook('extendForm', $schema);

        return $schema;
    }

    protected function getBasePrices(): array
    {
        // Get enabled currencies
        $currencies = Currency::whereEnabled(true)
            ->orderBy('default', 'desc')
            ->orderBy('name')
            ->get();

        $prices = collect([]);

        $basePrices = $this->getOwnerRecord()
            ->basePrices()
            ->with('currency')
            ->get()
            ->sortByDesc(fn ($p) => (int) $p->currency->default)
            ->values();

        foreach ($basePrices as $price) {
            $prices->put(
                $price->currency->code,
                [
                    'id' => $price->id,
                    'original_value' => $price->decimal('price', rounding: false),
                    'value' => $price->decimal('price', rounding: false),
                    'original_list_price' => $price->decimal('list_price', rounding: false),
                    'list_price' => $price->decimal('list_price', rounding: false),
                    'factor' => $price->currency->factor,
                    'label' => $price->currency->name,
                    'currency_code' => $price->currency->code,
                    'default_currency' => $price->currency->default,
                    'sync_prices' => $price->currency->sync_prices,
                    'currency_id' => $price->currency_id,
                ]
            );
        }

        $defaultCurrencyPrice = $prices->first(
            fn ($price) => $price['default_currency']
        );

        foreach ($currencies as $currency) {
            if (! $prices->get($currency->code)) {
                $value = round(($defaultCurrencyPrice['value'] ?? 0) * $currency->exchange_rate, $currency->decimal_places);
                $prices->put($currency->code, [
                    'id' => null,
                    'original_value' => $value,
                    'value' => $value,
                    'list_price' => round(($defaultCurrencyPrice['list_price'] ?? 0) * $currency->exchange_rate, $currency->decimal_places),
                    'factor' => $currency->factor,
                    'label' => $currency->name,
                    'currency_code' => $currency->code,
                    'default_currency' => $currency->default,
                    'sync_prices' => $currency->sync_prices,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        return $prices->values()->toArray();
    }
}
