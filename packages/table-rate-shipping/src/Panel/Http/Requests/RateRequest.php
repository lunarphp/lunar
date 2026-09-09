<?php

namespace Lunar\Shipping\Panel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Shipping\Models\ShippingMethod;

/**
 * A shipping rate as the slideout submits it: the method, an enabled flag,
 * base prices in major units keyed by currency code, and a tier list whose
 * threshold is a minimum spend or a minimum weight depending on the method.
 */
class RateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'shipping_method_id' => ['required', Rule::exists(ShippingMethod::class, 'id')],
            'enabled' => ['sometimes', 'boolean'],
            'base_prices' => ['present', 'array'],
            'base_prices.*' => ['nullable', 'numeric', 'min:0'],
            'tiers' => ['present', 'array'],
            'tiers.*.customer_group_id' => ['nullable', Rule::exists(CustomerGroup::class, 'id')],
            'tiers.*.currency_code' => ['required', Rule::exists(Currency::class, 'code')],
            'tiers.*.min_quantity' => ['required', 'numeric', 'min:0'],
            'tiers.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $default = Currency::getDefault();

            if ($default && blank($this->input("base_prices.{$default->code}"))) {
                $validator->errors()->add("base_prices.{$default->code}", __('shipping::zones.rate_default_price_required', ['currency' => $default->code]));
            }

            $method = ShippingMethod::find($this->input('shipping_method_id'));

            if (($method?->data['charge_by'] ?? 'cart_total') !== 'weight') {
                return;
            }

            // Weight tiers are stored as raw integers in the method's unit;
            // reject decimals rather than truncating them.
            foreach ((array) $this->input('tiers', []) as $index => $tier) {
                $minQuantity = $tier['min_quantity'] ?? null;

                if (is_numeric($minQuantity) && (float) $minQuantity !== floor((float) $minQuantity)) {
                    $validator->errors()->add("tiers.{$index}.min_quantity", __('shipping::zones.rate_weight_tier_integer'));
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rateAttributes(): array
    {
        $validated = $this->validated();

        return [
            'shipping_method_id' => (int) $validated['shipping_method_id'],
            'enabled' => (bool) ($validated['enabled'] ?? true),
            'base_prices' => $validated['base_prices'],
            'tiers' => $validated['tiers'],
        ];
    }
}
