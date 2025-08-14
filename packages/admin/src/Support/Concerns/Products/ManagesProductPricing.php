<?php

namespace Lunar\Admin\Support\Concerns\Products;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Models\Currency;
use Lunar\Models\Price;

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

        $prices = collect($data['basePrices']);
        unset($data['basePrices']);
        $variant->update($data);

        $prices->filter(
            fn ($price) => ! $price['id'] && isset($price['value'])
        )->each(fn ($price) => $variant->prices()->create([
            'currency_id' => $price['currency_id'],
            'price' => (int) round((float) ($price['value'] * $price['factor'])),
            'compare_price' => (int) round((float) ($price['compare_price'] * $price['factor'])),
            'min_quantity' => 1,
            'customer_group_id' => null,
        ])
        );

        $prices->filter(
            fn ($price) => $price['id'] && isset($price['value']) && ($price['value'] != $price['original_value'] || $price['compare_price'] != $price['original_compare_price'])
        )->each(fn ($price) => Price::find($price['id'])->update([
            'price' => (int) round((float) ($price['value'] * $price['factor'])),
            'compare_price' => (int) round((float) ($price['compare_price'] * $price['factor'])),
        ])
        );

        $this->basePrices = $this->getBasePrices();

        $this->dispatch('refresh-relation-manager');

        return $this->callLunarHook('afterUpdate', $record, $data);
    }

    public function getBasePriceFormSection(): Section
    {
        //        dd($this->basePrices);

        return Forms\Components\Section::make(
            __('lunarpanel::relationmanagers.pricing.form.basePrices.title')
        )
            ->schema(
                collect($this->basePrices)->map(callback: function ($price, $index): Forms\Components\Fieldset {
                    return Forms\Components\Fieldset::make($price['label'])->schema([
                        Forms\Components\TextInput::make('value')
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
                            ->hintIcon(function (Forms\Get $get, Forms\Components\TextInput $component) use ($index, $price) {
                                if (! ($price['sync_prices'] ?? false) && $get('basePrices.'.$index.'.id', true)) {
                                    return null;
                                }

                                return FilamentIcon::resolve('lunar::info');
                            })->hintIconTooltip(function (Forms\Get $get, Forms\Components\TextInput $component) use ($index, $price) {
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
                        Forms\Components\TextInput::make('compare_price')
                            ->label('')
                            ->statePath($index.'.compare_price')
                            ->numeric()
                            ->label(
                                __('lunarpanel::relationmanagers.pricing.form.basePrices.form.compare_price.label')
                            )
                            ->helperText(
                                __('lunarpanel::relationmanagers.pricing.form.basePrices.form.compare_price.helper_text')
                            )
                            ->hintColor('warning')
                            ->extraInputAttributes([
                                'class' => '',
                            ])
                            ->hintIcon(function (Forms\Get $get, Forms\Components\TextInput $component) use ($index, $price) {
                                if (! ($price['sync_prices'] ?? false) && $get('basePrices.'.$index.'.id', true)) {
                                    return null;
                                }

                                return FilamentIcon::resolve('lunar::info');
                            })->hintIconTooltip(function (Forms\Get $get, Forms\Components\TextInput $component) use ($index, $price) {
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

    public function form(Forms\Form $form): Forms\Form
    {
        if (! count($this->basePrices)) {
            $this->basePrices = $this->getBasePrices();
        }

        $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Group::make([
                    ProductVariantResource::getTaxClassIdFormComponent(),
                    ProductVariantResource::getTaxRefFormComponent(),
                ])->columns(2),
            ]),
            $this->getBasePriceFormSection(),
        ])->statePath('');

        $this->callLunarHook('extendForm', $form);

        return $form;
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
                    'original_value' => $price->price->decimal(rounding: false),
                    'value' => $price->price->decimal(rounding: false),
                    'original_compare_price' => $price->compare_price->decimal(rounding: false),
                    'compare_price' => $price->compare_price->decimal(rounding: false),
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
                    'compare_price' => round(($defaultCurrencyPrice['compare_price'] ?? 0) * $currency->exchange_rate, $currency->decimal_places),
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
