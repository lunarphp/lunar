<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Facades\OrderNotifications;

class NotifyOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'notification' => ['required', Rule::in(array_keys(OrderNotifications::sendable()))],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
