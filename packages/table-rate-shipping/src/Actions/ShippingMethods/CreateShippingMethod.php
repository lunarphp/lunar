<?php

namespace Lunar\Shipping\Actions\ShippingMethods;

use Lunar\Core\Facades\DB;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Shipping\Contracts\Actions\ShippingMethods\CreatesShippingMethod;
use Lunar\Shipping\Models\ShippingMethod;

/**
 * Create a shipping method and make it available to every customer group
 * from now, so a new method is offered at checkout until staff narrow it.
 */
class CreateShippingMethod implements CreatesShippingMethod
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ShippingMethod
    {
        return DB::transaction(function () use ($attributes): ShippingMethod {
            $attributes['data'] = $attributes['data'] ?? [];

            $shippingMethod = ShippingMethod::create($attributes);

            $shippingMethod->customerGroups()->sync(
                CustomerGroup::query()->pluck('id')->mapWithKeys(fn (int $id) => [$id => [
                    'visible' => true,
                    'enabled' => true,
                    'starts_at' => now(),
                ]])->all()
            );

            return $shippingMethod;
        });
    }
}
