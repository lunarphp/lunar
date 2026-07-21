<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Enums\ProductAssociation;
use Lunar\Core\Models\Product;

class ProductAssociationRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_column(ProductAssociation::cases(), 'value'))],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', Rule::exists((new Product)->getTable(), 'id')],
        ];
    }
}
