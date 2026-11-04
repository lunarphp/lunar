<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Lunar\Shipping\Contracts\Actions\ShippingMethods\CreatesShippingMethod;
use Lunar\Shipping\Panel\Http\Requests\MethodRequest;

class MethodCreateController
{
    public function store(MethodRequest $request, CreatesShippingMethod $createsShippingMethod): RedirectResponse
    {
        $attributes = $request->methodAttributes();

        // The create dialog carries the charge basis alongside the columns;
        // everything else about the driver is set on the edit screen.
        if ($chargeBy = $request->input('data.charge_by')) {
            $attributes['data'] = ['charge_by' => $chargeBy];
        }

        $method = $createsShippingMethod->execute($attributes);

        return redirect()
            ->route('panel.settings.shipping.methods.edit', $method)
            ->with('success', __('shipping::methods.flash_created'));
    }
}
