<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class MergeFulfilmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'target_id' => ['required', 'integer'],
        ];
    }
}
