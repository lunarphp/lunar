<?php

namespace Lunar\Panel\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\ProductType;
use Lunar\Core\States\Product\Draft;
use Lunar\Core\States\Product\Published;
use Lunar\Core\States\ProductType\Active;

/**
 * The minimal create flow: name, product type and status. Everything else is
 * edited on the product page after the redirect. Draft product types cannot
 * be chosen for new products (spec 0056's create-flow gating).
 */
class ProductStoreRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'product_type_id' => [
                'required',
                Rule::exists((new ProductType)->getTable(), 'id')->where('status', Active::$name),
            ],
            'status' => ['nullable', Rule::in([Published::$name, Draft::$name])],
        ];
    }
}
