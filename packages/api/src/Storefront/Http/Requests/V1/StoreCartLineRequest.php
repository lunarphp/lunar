<?php

namespace Lunar\Api\Storefront\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'purchasable_id' => ['required', 'string'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'meta' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
