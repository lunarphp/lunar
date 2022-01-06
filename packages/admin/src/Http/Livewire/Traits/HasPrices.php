<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Hub\Actions\Pricing\UpdateCustomerGroupPricing;
use GetCandy\Hub\Actions\Pricing\UpdatePrices;
use GetCandy\Hub\Actions\Pricing\UpdateTieredPricing;
use GetCandy\Models\Currency;
use GetCandy\Models\Price;
use GetCandy\Models\TaxClass;
use GetCandy\Rules\MaxDecimalPlaces;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

trait HasPrices
{
    /**
     * The base prices array.
     *
     * @var array
     */
    public $basePrices = [];

    /**
     * Collection of customer group prices.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $customerGroupPrices;

    /**
     * The tiered prices array.
     *
     * @var array
     */
    public $tieredPrices = [];

    /**
     * The currency currency.
     *
     * @var \GetCandy\Models\Currency
     */
    public Currency $currency;

    /**
     * Whether customer group pricing is enabled.
     *
     * @var bool
     */
    public bool $customerPricingEnabled = false;

    /**
     * Return the model which has prices.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract protected function getPricedModel();

    /**
     * Mount the HasPrices trait.
     *
     * @return void
     */
    public function mountHasPrices()
    {
        $variant = $this->getPricedModel();

        $this->currency = $this->defaultCurrency;

        // Get all 1 tier prices
        $tierOnePrices = $variant->prices->filter(fn ($price) => $price->tier == 1);

        $this->basePrices = $this->mapBasePrices($tierOnePrices->collect());

        $this->customerGroupPrices = $this->mapCustomerGroupPrices($tierOnePrices);

        $this->customerPricingEnabled = (bool) $this->customerGroupPrices->count();

        // Set up initial tiered prices
        $this->tieredPrices = $this->mapTieredPrices(
            $variant->prices->filter(fn ($price) => $price->tier > 1)
        );
    }

    /**
     * Method to save pricing.
     *
     * @return void
     */
    public function savePricing()
    {
        $model = $this->getPricedModel();

        DB::transaction(function () use ($model) {
            app(UpdatePrices::class)->execute(
                $model,
                $this->basePrices
            );
        });

        $this->tieredPrices = app(UpdateTieredPricing::class)->execute(
            $model,
            $this->tieredPrices
        );

        DB::transaction(function () use ($model) {
            // Save customer group pricing.
            if (!$this->customerPricingEnabled) {
                // If customer group pricing isn't enabled, we need to remove the prices for customer groups.
                $model->prices()->whereNotNull('customer_group_id')->whereTier(1)->delete();
                $this->customerGroupPrices = collect();
            }

            $this->customerGroupPrices = app(UpdateCustomerGroupPricing::class)->execute(
                $model,
                $this->customerGroupPrices
            );
        });
    }

    /**
     * Method to add a tier to the stack.
     *
     * @return void
     */
    public function addTier()
    {
        $this->tieredPrices[] = [
            'customer_group_id' => '*',
            'tier'              => null,
            'prices'            => collect($this->basePrices)->map(function ($price) {
                return [
                    'price'       => null,
                    'currency_id' => $price['currency_id'],
                ];
            })->toArray(),
        ];
    }

    /**
     * Method to remove a tier from the stack.
     *
     * @param int $index
     *
     * @return void
     */
    public function removeTier($index)
    {
        unset($this->tieredPrices[$index]);
    }

    /**
     * Set the currency using the provided id.
     *
     * @param int|string $currencyId
     *
     * @return void
     */
    public function setCurrency($currencyId)
    {
        $this->currency = $this->currencies->first(fn ($currency) => $currency->id == $currencyId);
    }

    /**
     * Listener method when customer group pricing is toggled.
     *
     * @param bool $value
     *
     * @return void
     */
    public function updatedCustomerPricingEnabled($value)
    {
        if (!$value || $this->customerGroupPrices->count()) {
            return;
        }
        $groups = $this->customerGroupPrices->toArray();

        foreach ($this->customerGroups as $group) {
            if (!($groups[$group->id] ?? false)) {
                $groups[$group->id] = collect($this->basePrices)->map(function ($price) use ($group) {
                    return [
                        'price'             => $price['price'],
                        'currency_id'       => $price['currency_id'],
                        'customer_group_id' => $group->id,
                    ];
                })->toArray();
            }
        }

        $this->customerGroupPrices = collect($groups);
    }

    /**
     * Return the computed customer groups.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    abstract public function getCustomerGroupsProperty();

    /**
     * Returns the currencies computed property.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCurrenciesProperty()
    {
        return Currency::get();
    }

    /**
     * Return the computed default currency.
     *
     * @return void
     */
    public function getDefaultCurrencyProperty()
    {
        return Currency::getDefault();
    }

    /**
     * Return the available tax classes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTaxClassesProperty()
    {
        return TaxClass::all();
    }

    /**
     * Return mapped base prices.
     *
     * @param \Illuminate\Support\Collection $prices
     *
     * @return \Illuminate\Support\Collection
     */
    private function mapBasePrices(Collection $prices)
    {
        $prices = $prices->filter(fn ($price) => !$price->customer_group_id)
            ->mapWithKeys(function ($price) {
                return [
                    $price->currency->code => [
                        'id'            => $price->id,
                        'currency_id'   => $price->currency_id,
                        'price'         => $price->price->decimal,
                        'compare_price' => $price->compare_price->value ? $price->compare_price->decimal : null,
                    ],
                ];
            });

        // If we have any currencies we don't have a price for
        // we need to add them.
        foreach ($this->currencies as $currency) {
            if (empty($prices[$currency->code])) {
                $prices[$currency->code] = [
                    'price'         => null,
                    'compare_price' => null,
                    'currency_id'   => $currency->id,
                ];
            }
        }

        return $prices;
    }

    /**
     * Return mapped customer group prices.
     *
     * @param \Illuminate\Support\Collection $prices
     *
     * @return \Illuminate\Support\Collection
     */
    private function mapCustomerGroupPrices(Collection $prices)
    {
        return $prices->filter(fn ($price) => (bool) $price->customer_group_id)
            ->groupBy('customer_group_id')
            ->mapWithKeys(function ($prices, $groupId) {
                foreach ($this->currencies as $currency) {
                    if (!$prices->first(fn ($price) => $price->currency_id == $currency->id)) {
                        $prices->push(new Price([
                            'customer_group_id' => $groupId,
                            'currency_id'       => $currency->id,
                        ]));
                    }
                }

                return [
                    $groupId => $prices->mapWithKeys(function ($price) {
                        return [
                            $price->currency->code => [
                                'id'                => $price->id,
                                'currency_id'       => $price->currency_id,
                                'customer_group_id' => $price->customer_group_id,
                                'price'             => $price->price->decimal,
                                'compare_price'     => $price->compare_price->decimal,
                            ],
                        ];
                    }),
                ];
            });
    }

    /**
     * Return mapped tiered pricing.
     *
     * @param \Illuminate\Support\Collection $prices
     *
     * @return \Illuminate\Support\Collection
     */
    private function mapTieredPrices(Collection $prices)
    {
        $data = collect();

        foreach ($prices->groupBy(['tier', 'customer_group_id']) as $customerGroups) {
            $data = $data->concat(
                $customerGroups->map(function ($prices, $tier) {
                    $default = $prices->first(fn ($price) => $price->currency->default);

                    $prices = $prices->mapWithKeys(function ($price) {
                        return [
                            $price->currency->code => [
                                'id'                => $price->id,
                                'currency_id'       => $price->currency_id,
                                'customer_group_id' => $price->customer_group_id,
                                'price'             => $price->price->decimal,
                                'compare_price'     => $price->compare_price->decimal,
                            ],
                        ];
                    });

                    foreach ($this->currencies as $currency) {
                        if (empty($prices[$currency->code])) {
                            $prices[$currency->code] = [
                                'price'       => null,
                                'currency_id' => $currency->id,
                            ];
                        }
                    }

                    return [
                        'customer_group_id' => $default->customer_group_id ?: '*',
                        'tier'              => $default->tier,
                        'prices'            => $prices,
                    ];
                })->values()
            );
        }

        return $data->sortBy('tier')->values();
    }

    /**
     * Define validation rules for images.
     *
     * @return bool
     */
    protected function hasPriceValidationRules()
    {
        $rules = [
            'customerPricingEnabled'           => 'boolean',
            'customerGroupPrices'              => 'nullable|array',
            'tieredPrices.*.tier'              => 'required|numeric|min:2',
            'tieredPrices.*.customer_group_id' => 'required',
        ];

        foreach ($this->currencies as $currency) {
            $rules['customerGroupPrices.*.'.$currency->code.'.price'] = [
                'nullable',
                'numeric',
                'max:10000000',
                'min:0.0001',
                new MaxDecimalPlaces($currency->decimal_places),
            ];

            $rules['basePrices.'.$currency->code.'.price'] = [
                'required',
                'numeric',
                'max:10000000',
                'min:0.0001',
                new MaxDecimalPlaces($currency->decimal_places),
            ];

            $rules['basePrices.'.$currency->code.'.compare_price'] = [
                'nullable',
                'numeric',
                'max:10000000',
                'min:0.0001',
                new MaxDecimalPlaces($currency->decimal_places),
            ];

            $rules['tieredPrices.*.prices.'.$currency->code.'.price'] = [
                'required',
                'numeric',
                'min:0.001',
                'max:'.($this->basePrices[$currency->code]['price'] ?? null) ?: 0.001,
                new MaxDecimalPlaces($currency->decimal_places),
            ];
        }

        return $rules;
    }

    /**
     * Return extra validation messages.
     *
     * @return array
     */
    protected function hasPriceValidationMessages()
    {
        $messages = [
            'tieredPrices.*.tier.required' => __('adminhub::validation.tier_required'),
        ];

        Validator::replacer(MaxDecimalPlaces::class, function ($message, $attribute, $rule, $parameters) {
            $currency = $this->currencies->first(function ($currency) use ($attribute) {
                return strpos($attribute, $currency->code) !== false;
            });

            if (!$currency) {
                return $message;
            }

            return __('adminhub::validation.max_decimals_currency', [
                'currency' => $currency->code,
                'decimals' => $currency->decimal_places,
            ]);
        });

        foreach ($this->currencies as $currency) {
            $tierKey = 'tieredPrices.*.prices.'.$currency->code.'.price';
            $baseKey = 'basePrices.'.$currency->code.'.price';
            $baseCompareKey = 'basePrices.'.$currency->code.'.compare_price';
            $customerKey = 'customerGroupPrices.*.'.$currency->code.'.price';
            $rules = ['max', 'numeric', 'required', 'min'];

            foreach ($rules as $rule) {
                $messages[$tierKey.'.'.$rule] = __('adminhub::validation.'.$rule.'_price_currency', [
                    'currency' => $currency->code,
                ]);
                $messages[$customerKey.'.'.$rule] = __('adminhub::validation.'.$rule.'_price_currency', [
                    'currency' => $currency->code,
                ]);
                $messages[$baseKey.'.'.$rule] = __('adminhub::validation.'.$rule.'_price_currency', [
                    'currency' => $currency->code,
                ]);
                $messages[$baseCompareKey.'.'.$rule] = __('adminhub::validation.'.$rule.'_price_currency', [
                    'currency' => $currency->code,
                ]);
            }
        }

        return $messages;
    }
}
