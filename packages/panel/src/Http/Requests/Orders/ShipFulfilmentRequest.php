<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class ShipFulfilmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tracking' => ['array'],
            'tracking.*.carrier' => ['nullable', 'string', 'max:255'],
            'tracking.*.shipping_method' => ['nullable', 'string', 'max:255'],
            'tracking.*.tracking_number' => ['nullable', 'string', 'max:255'],
            'tracking.*.tracking_url' => ['nullable', 'url', 'max:2000'],
            'notify' => ['boolean'],
        ];
    }
}
