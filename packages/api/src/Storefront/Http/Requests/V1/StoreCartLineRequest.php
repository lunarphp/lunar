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

    /** @return array<string, string> */
    public function descriptions(): array
    {
        return [
            'purchasable_id' => 'Public id of the variant to add. It must be enabled and its product visible to the request.',
            'quantity' => 'Units to add; defaults to 1.',
            'meta' => 'Arbitrary metadata stored on the line.',
        ];
    }
}
