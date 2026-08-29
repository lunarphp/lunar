<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class OrderNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
