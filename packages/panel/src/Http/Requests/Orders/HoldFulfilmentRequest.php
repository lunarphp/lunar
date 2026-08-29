<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Facades\HoldReasons;

class HoldFulfilmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', Rule::in(array_keys(HoldReasons::all()))],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
