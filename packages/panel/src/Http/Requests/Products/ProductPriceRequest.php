<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;

/**
 * Shared by the price store and update endpoints. Amounts arrive as integer
 * minor units (the client converts by the currency's decimal places). A
 * price row is unique per currency + customer group + minimum quantity on
 * its variant, so two rows can never compete for the same context.
 */
class ProductPriceRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var ProductVariant $variant */
        $variant = $this->route('productVariant');

        $unique = Rule::unique((new Price)->getTable(), 'currency_id')
            ->where('priceable_type', $variant->getMorphClass())
            ->where('priceable_id', $variant->getKey())
            ->where('customer_group_id', $this->input('customer_group_id') ?: null)
            ->where('min_quantity', (int) $this->input('min_quantity', 1));

        if ($price = $this->route('price')) {
            $unique->ignore($price->getKey());
        }

        return [
            'currency_id' => ['required', Rule::exists((new Currency)->getTable(), 'id'), $unique],
            'customer_group_id' => ['nullable', Rule::exists((new CustomerGroup)->getTable(), 'id')],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'list_price' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
