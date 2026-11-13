<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Facades\FulfilmentMethods;
use Lunar\Core\Models\Location;

/**
 * The target method, an optional location, and an optional shipping address
 * for a delivery method on an order that has none. The address takes the
 * order address rules, required only when an address is sent.
 */
class ChangeFulfilmentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $addressRules = collect((new OrderAddressRequest)->rules())
            ->mapWithKeys(fn (array $rules, string $field) => [
                "address.{$field}" => array_map(fn (string $rule) => $rule === 'required' ? 'required_with:address' : $rule, $rules),
            ])
            ->all();

        return [
            'method' => ['required', 'string', Rule::in(FulfilmentMethods::all()->keys()->all())],
            'location_id' => ['nullable', 'integer', Rule::exists((new Location)->getTable(), 'id')],
            'address' => ['nullable', 'array'],
            ...$addressRules,
        ];
    }
}
