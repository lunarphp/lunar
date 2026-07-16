<?php

namespace Lunar\Panel\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\CustomerGroup;

/** Shared by the customer store and update endpoints, whose rules are identical. */
class CustomerRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'account_ref' => ['nullable', 'string', 'max:255'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['integer', Rule::exists((new CustomerGroup)->getTable(), 'id')],
        ];
    }

    /** @return array<string, mixed> */
    public function customerAttributes(): array
    {
        return collect($this->validated())->except('customer_group_ids')->all();
    }

    /** @return array<int, int> */
    public function customerGroupIds(): array
    {
        return $this->validated()['customer_group_ids'] ?? [];
    }
}
