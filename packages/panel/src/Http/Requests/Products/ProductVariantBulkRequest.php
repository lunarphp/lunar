<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\ProductVariant;

class ProductVariantBulkRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'op' => ['required', Rule::in(['enable', 'disable', 'destroy', 'price', 'stock'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists((new ProductVariant)->getTable(), 'id')],
            // Minor units for price, whole units for stock.
            'value' => ['nullable', 'required_if:op,price,stock', 'integer', 'min:0'],
        ];
    }
}
