<?php

namespace Lunar\Shipping\Panel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Product;
use Lunar\Shipping\Models\ShippingExclusionList;

class ExclusionListRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(ShippingExclusionList::class, 'name')->ignore($this->route('shippingExclusionList'))],
            'products' => ['sometimes', 'array'],
            'products.*' => [Rule::exists(Product::class, 'id')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function listAttributes(): array
    {
        $validated = $this->validated();

        $attributes = ['name' => $validated['name']];

        if (array_key_exists('products', $validated)) {
            $attributes['products'] = $validated['products'];
        }

        return $attributes;
    }
}
