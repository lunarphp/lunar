<?php

namespace Lunar\Panel\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Lunar\Core\Models\Country;

/** Shared by the customer address store and update endpoints. */
class CustomerAddressRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'line_one' => ['required', 'string', 'max:255'],
            'line_two' => ['nullable', 'string', 'max:255'],
            'line_three' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:'.(new Country)->getTable().',id'],
            'delivery_instructions' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'shipping_default' => ['nullable', 'boolean'],
            'billing_default' => ['nullable', 'boolean'],
        ];
    }
}
