<?php

namespace Lunar\Panel\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

class LinkCustomerUserRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
