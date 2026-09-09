<?php

namespace Lunar\Shipping\Actions\ShippingMethods;

use Lunar\Core\Facades\DB;
use Lunar\Shipping\Contracts\Actions\ShippingMethods\UpdatesShippingMethod;
use Lunar\Shipping\Models\ShippingMethod;

/**
 * Update a shipping method and, when supplied, its driver data and customer
 * group availability.
 *
 * Beyond the method's own columns, the attributes may carry:
 * - `data` — merged key by key into the stored driver data; a null value
 *   removes that key. A driver's private keys survive an edit that only
 *   touches the ones the panel knows about.
 * - `customer_groups` — `{id, enabled, visible, starts_at, ends_at}` rows
 *   replacing the availability pivot
 */
class UpdateShippingMethod implements UpdatesShippingMethod
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ShippingMethod $shippingMethod, array $attributes): ShippingMethod
    {
        $data = $attributes['data'] ?? null;
        $customerGroups = $attributes['customer_groups'] ?? null;

        unset($attributes['data'], $attributes['customer_groups']);

        DB::transaction(function () use ($shippingMethod, $attributes, $data, $customerGroups): void {
            if ($data !== null) {
                $attributes['data'] = collect((array) $shippingMethod->data)
                    ->merge($data)
                    ->reject(fn ($value) => $value === null)
                    ->all();
            }

            $shippingMethod->update($attributes);

            if ($customerGroups !== null) {
                $shippingMethod->customerGroups()->sync(
                    collect($customerGroups)->mapWithKeys(fn (array $row) => [(int) $row['id'] => [
                        'enabled' => (bool) ($row['enabled'] ?? false),
                        'visible' => (bool) ($row['visible'] ?? false),
                        'starts_at' => $row['starts_at'] ?? null,
                        'ends_at' => $row['ends_at'] ?? null,
                    ]])->all()
                );
            }
        });

        return $shippingMethod;
    }
}
