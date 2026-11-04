<?php

namespace Lunar\Shipping\Panel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lunar\Core\Facades\Converter;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;

/**
 * Shared by the method store and update endpoints. The store endpoint carries
 * the method's own columns; the update endpoint may also carry the driver
 * `data` block and the customer-group availability rows.
 */
class MethodRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $weightUnits = array_keys(Converter::getMeasurements()['weight'] ?? []);

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique(ShippingMethod::class, 'code')->ignore($this->route('shippingMethod'))],
            'driver' => ['required', Rule::in(Shipping::getSupportedDrivers()->keys()->all())],
            'description' => ['nullable', 'string'],
            'stock_available' => ['sometimes', 'boolean'],
            'weight_unit' => ['nullable', Rule::in($weightUnits)],
            'min_weight' => ['nullable', 'numeric', 'min:0', 'required_with:weight_unit'],
            'max_weight' => ['nullable', 'numeric', 'min:0', 'required_with:weight_unit'],

            'data' => ['sometimes', 'array'],
            'data.charge_by' => ['nullable', Rule::in(['cart_total', 'weight'])],
            'data.use_discount_amount' => ['nullable', 'boolean'],
            'data.minimum_spend' => ['nullable', 'array'],
            'data.minimum_spend.*' => ['nullable', 'numeric', 'min:0'],
            'data.schedule' => ['nullable', 'array'],
            'data.schedule.*.enabled' => ['required', 'boolean'],
            'data.schedule.*.from' => ['nullable', 'date_format:H:i'],
            'data.schedule.*.to' => ['nullable', 'date_format:H:i'],

            'customer_groups' => ['sometimes', 'array'],
            'customer_groups.*.id' => ['required', Rule::exists(CustomerGroup::class, 'id')],
            'customer_groups.*.enabled' => ['required', 'boolean'],
            'customer_groups.*.visible' => ['required', 'boolean'],
            'customer_groups.*.starts_at' => ['nullable', 'date'],
            'customer_groups.*.ends_at' => ['nullable', 'date', 'after_or_equal:customer_groups.*.starts_at'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $min = $this->input('min_weight');
            $max = $this->input('max_weight');

            if (is_numeric($min) && is_numeric($max) && (float) $max <= (float) $min) {
                $validator->errors()->add('max_weight', __('shipping::methods.max_weight_after_min'));
            }

            foreach ((array) $this->input('data.schedule', []) as $day => $row) {
                $from = $row['from'] ?? null;
                $to = $row['to'] ?? null;

                if (($row['enabled'] ?? false) && $from && $to && $to <= $from) {
                    $validator->errors()->add("data.schedule.{$day}.to", __('shipping::methods.schedule_to_after_from'));
                }
            }
        });
    }

    /**
     * The validated input shaped for the method actions. Columns only; `data`
     * and `customer_groups` are returned separately by the accessors below so
     * the controller can scale money inside `data` before handing it over.
     *
     * @return array<string, mixed>
     */
    public function methodAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'driver' => $validated['driver'],
        ];

        foreach (['description', 'weight_unit', 'min_weight', 'max_weight'] as $nullable) {
            if (array_key_exists($nullable, $validated)) {
                $attributes[$nullable] = $validated[$nullable];
            }
        }

        if (array_key_exists('stock_available', $validated)) {
            $attributes['stock_available'] = (bool) $validated['stock_available'];
        }

        // Weight limits mean nothing without a unit; an unset unit clears them.
        if (array_key_exists('weight_unit', $validated) && blank($validated['weight_unit'])) {
            $attributes['weight_unit'] = null;
            $attributes['min_weight'] = null;
            $attributes['max_weight'] = null;
        }

        return $attributes;
    }

    /**
     * The driver data block as submitted (money still in major units), or
     * null when the request did not carry one.
     *
     * @return array<string, mixed>|null
     */
    public function driverData(): ?array
    {
        $validated = $this->validated();

        return array_key_exists('data', $validated) ? (array) $validated['data'] : null;
    }

    /**
     * @return array<int, array{id: int, enabled: bool, visible: bool, starts_at: ?string, ends_at: ?string}>|null
     */
    public function customerGroupRows(): ?array
    {
        $validated = $this->validated();

        if (! array_key_exists('customer_groups', $validated)) {
            return null;
        }

        return collect($validated['customer_groups'])->map(fn (array $row) => [
            'id' => (int) $row['id'],
            'enabled' => (bool) $row['enabled'],
            'visible' => (bool) $row['visible'],
            'starts_at' => $row['starts_at'] ?? null,
            'ends_at' => $row['ends_at'] ?? null,
        ])->values()->all();
    }
}
