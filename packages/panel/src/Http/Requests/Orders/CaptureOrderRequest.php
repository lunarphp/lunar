<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CaptureOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
