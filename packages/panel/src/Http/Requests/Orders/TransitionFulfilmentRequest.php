<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class TransitionFulfilmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'state' => ['required', 'string', 'max:255'],
            'notify' => ['boolean'],
        ];
    }
}
