<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Facades\CancelReasons;

class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', Rule::in(array_keys(CancelReasons::all()))],
            'note' => ['nullable', 'string', 'max:2000'],
            'notify' => ['boolean'],
        ];
    }
}
