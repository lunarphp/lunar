<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;

class ProductAssociationRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(ProductAssociation::getTypes()))],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', Rule::exists((new Product)->getTable(), 'id')],
        ];
    }
}
