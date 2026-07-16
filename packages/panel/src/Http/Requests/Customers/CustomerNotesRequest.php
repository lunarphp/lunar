<?php

namespace Lunar\Panel\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

class CustomerNotesRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'admin_notes' => ['present', 'nullable', 'string', 'max:65535'],
        ];
    }
}
